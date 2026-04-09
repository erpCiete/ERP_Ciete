import { useEffect, useRef } from 'react';

const TWO_PI = Math.PI * 2;
const LOGO_VIEWBOX = 136;
const BASE_RECT = { x: 20, y: 20, w: 64, h: 64 };
const SECOND_RECT = { x: 52, y: 52, w: 64, h: 64 };
const OVERLAP_RECT = { x: 52, y: 52, w: 32, h: 32 };

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

function isInsideRect(rect, x, y) {
    return x >= rect.x && x <= rect.x + rect.w && y >= rect.y && y <= rect.y + rect.h;
}

function readCssVar(styles, token, fallback) {
    const resolved = styles.getPropertyValue(token).trim();
    return resolved || fallback;
}

function getLogoPalette() {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return {
            red: '#FE0000',
            blue: '#003EFF',
            overlap: '#0A0A0A',
        };
    }

    const styles = window.getComputedStyle(document.documentElement);
    return {
        red: readCssVar(styles, '--prueba-logo-red', '#FE0000'),
        blue: readCssVar(styles, '--prueba-logo-blue', '#003EFF'),
        overlap: readCssVar(styles, '--prueba-logo-overlap', '#0A0A0A'),
    };
}

function resolveLogoColor(logoX, logoY, palette) {
    const inBase = isInsideRect(BASE_RECT, logoX, logoY);
    const inSecond = isInsideRect(SECOND_RECT, logoX, logoY);
    const inOverlap = isInsideRect(OVERLAP_RECT, logoX, logoY);

    if (inOverlap) {
        return palette.overlap;
    }

    if (inSecond) {
        return palette.blue;
    }

    if (inBase) {
        return palette.red;
    }

    return null;
}

function buildParticles(width, height, shouldReduceMotion, palette) {
    const shortSide = Math.min(width, height);
    const side = shortSide * 0.8;
    const half = side / 2;

    const compact = width < 480;
    const spacing = shouldReduceMotion ? 9 : compact ? 7 : 6;
    const maxParticles = shouldReduceMotion ? 900 : compact ? 1300 : 1900;

    const originX = width / 2;
    const originY = height / 2;

    const generated = [];

    for (let y = -half; y <= half; y += spacing) {
        for (let x = -half; x <= half; x += spacing) {
            const logoX = ((x + half) / side) * LOGO_VIEWBOX;
            const logoY = ((y + half) / side) * LOGO_VIEWBOX;
            const color = resolveLogoColor(logoX, logoY, palette);

            if (!color) {
                continue;
            }

            generated.push({
                ox: originX + x + (Math.random() - 0.5) * spacing * 0.22,
                oy: originY + y + (Math.random() - 0.5) * spacing * 0.22,
                x: originX + x,
                y: originY + y,
                vx: 0,
                vy: 0,
                size: Math.random() < 0.58 ? 1.45 : 1.2,
                phase: Math.random() * TWO_PI,
                color,
            });
        }
    }

    if (generated.length <= maxParticles) {
        return generated;
    }

    const stride = Math.ceil(generated.length / maxParticles);
    return generated.filter((_, index) => index % stride === 0);
}

export default function CieteParticleLogoCanvas({
    className = '',
    shouldReduceMotion = false,
    themeKey = 'light',
}) {
    const wrapperRef = useRef(null);
    const canvasRef = useRef(null);

    useEffect(() => {
        const wrapper = wrapperRef.current;
        const canvas = canvasRef.current;
        if (!wrapper || !canvas) {
            return undefined;
        }

        const context = canvas.getContext('2d');
        if (!context) {
            return undefined;
        }

        const pointer = { x: 0, y: 0, active: false };
        const hasCoarsePointer = window.matchMedia('(pointer: coarse)').matches;
        const pointerEnabled = !shouldReduceMotion && !hasCoarsePointer;

        let particles = [];
        let frameId = 0;
        let width = 0;
        let height = 0;
        let lastTimestamp = performance.now();
        let documentVisible = typeof document === 'undefined' ? true : document.visibilityState !== 'hidden';
        let viewportVisible = true;

        const canAnimate = () => documentVisible && viewportVisible;

        const renderParticles = (timestamp) => {
            if (!width || !height) {
                return;
            }

            const frameDelta = clamp((timestamp - lastTimestamp) / 16.667, 0.65, 2.1);
            lastTimestamp = timestamp;

            context.clearRect(0, 0, width, height);

            const idleAmplitude = shouldReduceMotion ? 0.16 : 0.78;
            const spring = shouldReduceMotion ? 0.02 : 0.055;
            const dampingBase = shouldReduceMotion ? 0.91 : 0.83;
            const damping = Math.pow(dampingBase, frameDelta);
            const interactionRadius = width < 480 ? 86 : 108;
            const interactionPower = width < 480 ? 1.18 : 1.45;

            for (const particle of particles) {
                const idleX = Math.sin(timestamp * 0.0012 + particle.phase) * idleAmplitude;
                const idleY = Math.cos(timestamp * 0.001 + particle.phase * 1.17) * idleAmplitude;
                const targetX = particle.ox + idleX;
                const targetY = particle.oy + idleY;

                if (pointerEnabled && pointer.active) {
                    const dx = particle.x - pointer.x;
                    const dy = particle.y - pointer.y;
                    const distance = Math.hypot(dx, dy);

                    if (distance < interactionRadius) {
                        const force = 1 - distance / interactionRadius;
                        const normalizedX = dx / (distance || 1);
                        const normalizedY = dy / (distance || 1);

                        particle.vx += normalizedX * force * interactionPower;
                        particle.vy += normalizedY * force * interactionPower;
                    }
                }

                particle.vx += (targetX - particle.x) * spring * frameDelta;
                particle.vy += (targetY - particle.y) * spring * frameDelta;
                particle.vx *= damping;
                particle.vy *= damping;
                particle.x += particle.vx * frameDelta;
                particle.y += particle.vy * frameDelta;

                context.beginPath();
                context.fillStyle = particle.color;
                context.arc(particle.x, particle.y, particle.size, 0, TWO_PI);
                context.fill();
            }
        };

        const stopAnimation = () => {
            if (!frameId) {
                return;
            }

            window.cancelAnimationFrame(frameId);
            frameId = 0;
        };

        const renderFrame = (timestamp) => {
            renderParticles(timestamp);

            if (!canAnimate()) {
                frameId = 0;
                return;
            }

            frameId = window.requestAnimationFrame(renderFrame);
        };

        const startAnimation = () => {
            if (frameId || !canAnimate()) {
                return;
            }

            lastTimestamp = performance.now();
            frameId = window.requestAnimationFrame(renderFrame);
        };

        const updateCanvasSize = () => {
            const rect = wrapper.getBoundingClientRect();
            width = Math.max(180, Math.floor(rect.width));
            height = Math.max(180, Math.floor(rect.height));

            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.floor(width * dpr);
            canvas.height = Math.floor(height * dpr);
            canvas.style.width = `${width}px`;
            canvas.style.height = `${height}px`;
            context.setTransform(dpr, 0, 0, dpr, 0, 0);

            const palette = getLogoPalette();
            particles = buildParticles(width, height, shouldReduceMotion, palette);
            renderParticles(performance.now());
        };

        const onPointerMove = (event) => {
            const rect = canvas.getBoundingClientRect();
            pointer.x = event.clientX - rect.left;
            pointer.y = event.clientY - rect.top;
            pointer.active = true;
        };

        const onPointerLeave = () => {
            pointer.active = false;
        };

        const onVisibilityChange = () => {
            documentVisible = document.visibilityState !== 'hidden';

            if (documentVisible) {
                startAnimation();
                return;
            }

            stopAnimation();
        };

        updateCanvasSize();

        let resizeObserver;
        if (typeof ResizeObserver !== 'undefined') {
            resizeObserver = new ResizeObserver(updateCanvasSize);
            resizeObserver.observe(wrapper);
        } else {
            window.addEventListener('resize', updateCanvasSize);
        }

        if (pointerEnabled) {
            wrapper.addEventListener('pointermove', onPointerMove);
            wrapper.addEventListener('pointerleave', onPointerLeave);
        }

        let intersectionObserver;
        if (typeof IntersectionObserver !== 'undefined') {
            intersectionObserver = new IntersectionObserver(
                ([entry]) => {
                    viewportVisible = Boolean(entry?.isIntersecting);

                    if (viewportVisible) {
                        startAnimation();
                        return;
                    }

                    stopAnimation();
                },
                { threshold: 0.08 },
            );

            intersectionObserver.observe(wrapper);
        }

        if (typeof document !== 'undefined') {
            document.addEventListener('visibilitychange', onVisibilityChange);
        }

        startAnimation();

        return () => {
            stopAnimation();
            if (resizeObserver) {
                resizeObserver.disconnect();
            } else {
                window.removeEventListener('resize', updateCanvasSize);
            }

            if (intersectionObserver) {
                intersectionObserver.disconnect();
            }

            if (typeof document !== 'undefined') {
                document.removeEventListener('visibilitychange', onVisibilityChange);
            }

            if (pointerEnabled) {
                wrapper.removeEventListener('pointermove', onPointerMove);
                wrapper.removeEventListener('pointerleave', onPointerLeave);
            }
        };
    }, [shouldReduceMotion, themeKey]);

    const wrapperClassName = ['relative overflow-hidden', className].filter(Boolean).join(' ');

    return (
        <div ref={wrapperRef} className={wrapperClassName}>
            {/* Canvas sin elementos DOM por particula para mantener rendimiento estable. */}
            <canvas ref={canvasRef} className="h-full w-full" aria-hidden />
        </div>
    );
}
