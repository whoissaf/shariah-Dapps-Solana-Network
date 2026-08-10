import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';

export default function ReportsPage() {
  return (
    <>
      <Topbar title="Reports" subtitle="Export compliance reports in CSV format." />
      <Card>
        <div className="empty-state">
          <div className="icon">◨</div>
          <div style={{ fontWeight: 600, color: 'var(--color-text-primary)' }}>
            Report exports
          </div>
          <div style={{ fontSize: 13 }}>
            CSV export and report filters will be implemented next.
          </div>
        </div>
      </Card>
    </>
  );
}
