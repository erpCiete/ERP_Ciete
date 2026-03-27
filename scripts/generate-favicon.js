const fs = require('fs');
const path = require('path');
const sharp = require('sharp');
const pngToIco = require('png-to-ico');

async function generate() {
    const input = path.resolve(__dirname, '../public/images/logo/Ciete-Ingenieros-SA.webp');
    const outDir = path.resolve(__dirname, '../public');
    if (!fs.existsSync(input)) {
        console.error('Input image not found:', input);
        process.exit(1);
    }

    // sizes for favicon
    const sizes = [16, 32, 48, 64];
    const tmpFiles = [];

    for (const size of sizes) {
        const tmp = path.resolve(__dirname, `../.tmp/favicon-${size}.png`);
        await sharp(input).resize(size, size).png().toFile(tmp);
        tmpFiles.push(tmp);
    }

    const pngBuffers = tmpFiles.map((f) => fs.readFileSync(f));
    const icoBuffer = await pngToIco(pngBuffers);
    const outPath = path.join(outDir, 'favicon.ico');
    fs.writeFileSync(outPath, icoBuffer);
    console.log('favicon.ico written to', outPath);

    // cleanup tmp files
    for (const f of tmpFiles) {
        try { fs.unlinkSync(f); } catch (e) { }
    }
}

generate().catch((err) => {
    console.error(err);
    process.exit(1);
});
