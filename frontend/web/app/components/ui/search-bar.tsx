'use client';

export function SearchBar({
  value,
  onChange,
  placeholder = 'Search...',
}: {
  value: string;
  onChange: (v: string) => void;
  placeholder?: string;
}) {
  return (
    <div style={{ position: 'relative', flex: 1 }}>
      <span style={{
        position: 'absolute',
        left: 12,
        top: '50%',
        transform: 'translateY(-50%)',
        color: 'var(--color-text-muted)',
        fontSize: 14,
      }}>
        🔍
      </span>
      <input
        className="input"
        type="text"
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        style={{ paddingLeft: 36 }}
      />
    </div>
  );
}
