<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Server, ShoppingBag, FileText, MessageSquare, Globe, Shield, Users, Settings, Ticket, CreditCard, Cog, History, ExternalLink } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const page = usePage();
const user = page.props.auth?.user;
const isAdmin = user?.role?.slug === 'admin' || user?.role?.slug === 'super_admin';

const adminNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    { title: 'Customers', href: '/admin/customers', icon: Users },
    { title: 'Products', href: '/admin/products', icon: ShoppingBag },
    { title: 'Orders', href: '/admin/orders', icon: ShoppingBag },
    { title: 'Services', href: '/admin/services', icon: Server },
    { title: 'Invoices', href: '/admin/invoices', icon: FileText },
    { title: 'Payments', href: '/admin/payments', icon: CreditCard },
    { title: 'Tickets', href: '/admin/tickets', icon: Ticket },
    { title: 'Domains', href: '/admin/domains', icon: Globe },
    { title: 'Hosting', href: '/admin/hosting', icon: Server },
    { title: 'VPS', href: '/admin/vps', icon: Shield },
    { title: 'Proxmox', href: '/admin/proxmox', icon: Server },
    { title: 'Hosting Servers', href: '/admin/hosting-servers', icon: Server },
    { title: 'Coupons', href: '/admin/coupons', icon: Ticket },
    { title: 'Settings', href: '/admin/settings', icon: Cog },
    { title: 'Users', href: '/admin/users', icon: Users },
    { title: 'Audit Logs', href: '/admin/audit-logs', icon: History },
];

const customerNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard', icon: LayoutGrid },
    { title: 'My Services', href: '/customer/services', icon: Server },
    { title: 'Orders', href: '/customer/orders', icon: ShoppingBag },
    { title: 'Invoices', href: '/customer/invoices', icon: FileText },
    { title: 'Payments', href: '/customer/payments', icon: CreditCard },
    { title: 'Tickets', href: '/customer/tickets', icon: Ticket },
    { title: 'Domains', href: '/customer/domains', icon: Globe },
    { title: 'VPS', href: '/customer/vps', icon: Shield },
    { title: 'Hosting', href: '/customer/hosting-services', icon: Server },
    { title: 'Notifications', href: '/customer/notifications', icon: ExternalLink },
];

const publicNavItems: NavItem[] = [
    { title: 'Home', href: '/home', icon: LayoutGrid },
    { title: 'Hosting', href: '/hosting', icon: Server },
    { title: 'VPS', href: '/vps', icon: Shield },
    { title: 'Domain', href: '/domain', icon: Globe },
    { title: 'Pricing', href: '/pricing', icon: CreditCard },
];

const mainNavItems = isAdmin ? adminNavItems : (user ? customerNavItems : publicNavItems);

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="isAdmin ? '/admin/dashboard' : '/customer/dashboard'">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>