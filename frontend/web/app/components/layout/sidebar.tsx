'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';

const menuItems = [
  { href: '/dashboard', label: 'Dashboard', icon: '◧' },
  { href: '/dashboard/verification', label: 'Verification', icon: '✓' },
  { href: '/dashboard/audit', label: 'Audit Trail', icon: '⊙' },
  { href: '/dashboard/reports', label: 'Reports', icon: '◨' },
  { href: '/dashboard/profile', label: 'Profile', icon: '◉' },
];

export function Sidebar() {
  const pathname = usePathname();

  return (
    <aside className="sidebar">
      <div className="sidebar-brand">
        <div className="sidebar-logo">✓</div>
        <div>
          <div className="sidebar-title">Verifier</div>
          <div className="sidebar-subtitle">Compliance Portal</div>
        </div>
      </div>

      <nav className="sidebar-nav">
        {menuItems.map((item) => {
          const isActive = pathname === item.href || 
            (item.href !== '/dashboard' && pathname.startsWith(item.href));
          return (
            <Link
              key={item.href}
              href={item.href}
              className={`sidebar-item ${isActive ? 'sidebar-item-active' : ''}`}
            >
              <span className="sidebar-icon">{item.icon}</span>
              <span className="sidebar-label">{item.label}</span>
            </Link>
          );
        })}
      </nav>

      <div className="sidebar-footer">
        <div className="sidebar-user">
          <div className="sidebar-avatar">V</div>
          <div>
            <div className="sidebar-user-name">Verifier Demo</div>
            <div className="sidebar-user-role">Compliance Officer</div>
          </div>
        </div>
      </div>
    </aside>
  );
}
