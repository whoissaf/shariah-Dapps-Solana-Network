type Kind = 'success' | 'warning' | 'error' | 'info' | 'neutral';

export function Badge({ label, kind = 'neutral' }: { label: string; kind?: Kind }) {
  return <span className={`badge badge-${kind}`}>{label}</span>;
}
