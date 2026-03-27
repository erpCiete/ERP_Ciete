import {
    BarChart3,
    Briefcase,
    FileText,
    FolderOpen,
    Home,
    LayoutDashboard,
    LogIn,
    LogOut,
    MapPin,
    Settings,
    Shield,
    ShoppingCart,
    Users,
} from 'lucide-react';

const ICON_BY_KEY = {
    'nav.home': Home,
    'nav.dashboard': LayoutDashboard,
    'nav.projects': FolderOpen,
    'nav.closurePanel': FileText,
    'nav.adminPanel': Shield,
    'nav.clients': Users,
    'nav.stations': MapPin,
    'nav.works': Briefcase,
    'nav.orders': ShoppingCart,
    'nav.legalizations': FileText,
    'nav.reports': BarChart3,
    'nav.configuration': Settings,
    'common.actions.logOut': LogOut,
    'nav.login': LogIn,
};

export function getNavigationIcon(key) {
    return ICON_BY_KEY[key] ?? null;
}
