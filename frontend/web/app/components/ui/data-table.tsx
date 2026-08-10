import type { ReactNode } from 'react';

export type Column<T> = {
  key: string;
  label: string;
  render?: (row: T) => ReactNode;
  className?: string;
};

export function DataTable<T extends { id: string | number }>({
  columns,
  rows,
  emptyText = 'No data available',
  emptyIcon = '◨',
}: {
  columns: Column<T>[];
  rows: T[];
  emptyText?: string;
  emptyIcon?: string;
}) {
  if (rows.length === 0) {
    return (
      <div className="empty-state">
        <div className="icon">{emptyIcon}</div>
        <div style={{ fontWeight: 600, color: 'var(--color-text-primary)' }}>
          {emptyText}
        </div>
      </div>
    );
  }

  return (
    <div style={{ overflowX: 'auto' }}>
      <table className="data-table">
        <thead>
          <tr>
            {columns.map((col) => (
              <th key={col.key}>{col.label}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={row.id}>
              {columns.map((col) => (
                <td key={col.key} className={col.className}>
                  {col.render ? col.render(row) : String((row as any)[col.key] ?? '')}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
