import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';

export default function VerificationPage() {
  return (
    <>
      <Topbar title="Verification" subtitle="Manage pending verification requests." />
      <Card>
        <div className="empty-state">
          <div className="icon">✓</div>
          <div style={{ fontWeight: 600, color: 'var(--color-text-primary)' }}>
            Verification queue
          </div>
          <div style={{ fontSize: 13 }}>
            Full verification queue screen will be implemented in the next stage.
          </div>
        </div>
      </Card>
    </>
  );
}
