import type { ReactNode } from 'react';

export function Button({
  children,
  variant = 'primary',
  onClick,
}: {
  children: ReactNode;
  variant?: 'primary' | 'outline';
  onClick?: () => void;
}) {
  return (
    <button className={`btn btn-${variant}`} onClick={onClick} type="button">
      {children}
    </button>
  );
}
