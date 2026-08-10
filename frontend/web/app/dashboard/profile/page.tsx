import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';
import { Badge } from '../../components/badge';

export default function ProfilePage() {
  return (
    <>
      <Topbar title="Profile" subtitle="Your verifier account information." />
      <Card>
        <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 24 }}>
          <div style={{
            width: 64,
            height: 64,
            borderRadius: '50%',
            backgroundColor: 'var(--color-surface-muted)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 24,
            fontWeight: 700,
            border: '1px solid var(--color-border)',
          }}>
            V
          </div>
          <div>
            <div style={{ fontSize: 18, fontWeight: 700 }}>Verifier Demo</div>
            <div style={{ fontSize: 13, color: 'var(--color-text-secondary)' }}>
              verifier@example.com
            </div>
            <div style={{ marginTop: 6 }}>
              <Badge label="Compliance Officer" kind="success" />
            </div>
          </div>
        </div>

        <div style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(2, 1fr)',
          gap: 16,
          padding: 16,
          backgroundColor: 'var(--color-surface-muted)',
          borderRadius: 'var(--radius-md)',
        }}>
          <div>
            <div className="label">Role</div>
            <div style={{ fontWeight: 500 }}>Verifier</div>
          </div>
          <div>
            <div className="label">Organization</div>
            <div style={{ fontWeight: 500 }}>Demo Bank</div>
          </div>
          <div>
            <div className="label">Last Login</div>
            <div className="mono">Today, 10:24 AM</div>
          </div>
          <div>
            <div className="label">Account Status</div>
            <Badge label="Active" kind="success" />
          </div>
        </div>
      </Card>
    </>
  );
}
