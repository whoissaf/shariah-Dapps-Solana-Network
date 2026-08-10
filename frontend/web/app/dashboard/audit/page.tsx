import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';

export default function AuditPage() {
  return (
    <>
      <Topbar title="Audit Trail" subtitle="Complete history of verification decisions." />
      <Card>
        <div className="empty-state">
          <div className="icon">⊙</div>
          <div style={{ fontWeight: 600, color: 'var(--color-text-primary)' }}>
            Audit timeline
          </div>
          <div style={{ fontSize: 13 }}>
            Full audit trail with blockchain linkage will be implemented next.
          </div>
        </div>
      </Card>
    </>
  );
}
