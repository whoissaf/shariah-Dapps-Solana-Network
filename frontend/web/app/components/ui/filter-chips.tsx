'use client';

type Chip = {
  id: string;
  label: string;
  count?: number;
};

export function FilterChips({
  chips,
  activeId,
  onSelect,
}: {
  chips: Chip[];
  activeId: string;
  onSelect: (id: string) => void;
}) {
  return (
    <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
      {chips.map((chip) => {
        const isActive = chip.id === activeId;
        return (
          <button
            key={chip.id}
            type="button"
            onClick={() => onSelect(chip.id)}
            style={{
              padding: '6px 12px',
              borderRadius: 'var(--radius-pill)',
              border: `1px solid ${isActive ? 'var(--color-primary)' : 'var(--color-border)'}`,
              backgroundColor: isActive ? 'rgba(16, 185, 129, 0.08)' : 'transparent',
              color: isActive ? 'var(--color-primary-dark)' : 'var(--color-text-secondary)',
              fontSize: 12,
              fontWeight: 600,
              cursor: 'pointer',
              transition: 'all 0.15s ease',
              fontFamily: 'var(--font-sans)',
            }}
          >
            {chip.label}
            {chip.count !== undefined && (
              <span style={{
                marginLeft: 6,
                padding: '1px 6px',
                borderRadius: 'var(--radius-pill)',
                backgroundColor: isActive ? 'rgba(16, 185, 129, 0.2)' : 'var(--color-surface-muted)',
                fontSize: 10,
              }}>
                {chip.count}
              </span>
            )}
          </button>
        );
      })}
    </div>
  );
}
