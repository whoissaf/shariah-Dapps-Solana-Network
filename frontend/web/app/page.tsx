import { Badge } from './components/badge';
import { Button } from './components/button';
import { Card } from './components/card';

export default function HomePage() {
  return (
    <main className="app-container">
      <header style={{ marginBottom: 32 }}>
        <div
          style={{
            width: 56,
            height: 56,
            borderRadius: 14,
            backgroundColor: 'var(--color-primary)',
            display: 'inline-flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#fff',
            fontSize: 24,
            marginBottom: 20,
          }}
        >
          ✓
        </div>
        <h1>Verifier Portal</h1>
        <p style={{ color: 'var(--color-text-secondary)', marginTop: 6 }}>
          Enterprise compliance dashboard for zero-knowledge proof verification.
        </p>
      </header>

      <section className="bento-grid">
        <Card>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
            <h3>Pending</h3>
            <Badge label="Queue" kind="info" />
          </div>
          <div style={{ fontSize: 28, fontWeight: 700 }}>0</div>
          <p style={{ color: 'var(--color-text-secondary)', fontSize: 12, marginTop: 6 }}>
            Awaiting verification
          </p>
        </Card>

        <Card>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
            <h3>Verified</h3>
            <Badge label="Active" kind="success" />
          </div>
          <div style={{ fontSize: 28, fontWeight: 700 }}>0</div>
          <p style={{ color: 'var(--color-text-secondary)', fontSize: 12, marginTop: 6 }}>
            Approved on-chain
          </p>
        </Card>

        <Card>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
            <h3>Rejected</h3>
            <Badge label="Closed" kind="error" />
          </div>
          <div style={{ fontSize: 28, fontWeight: 700 }}>0</div>
          <p style={{ color: 'var(--color-text-secondary)', fontSize: 12, marginTop: 6 }}>
            Decision logged
          </p>
        </Card>
      </section>

      <section style={{ marginTop: 32 }}>
        <div className="section-header">
          <h3>Design System Preview</h3>
        </div>
        <Card>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            <label className="label">Email</label>
            <input className="input" placeholder="verifier@example.com" />
            <label className="label" style={{ marginTop: 8 }}>Blockchain TX</label>
            <div className="mono">0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb1</div>
            <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginTop: 8 }}>
              <Badge label="Success" kind="success" />
              <Badge label="Warning" kind="warning" />
              <Badge label="Error" kind="error" />
              <Badge label="Info" kind="info" />
              <Badge label="Neutral" kind="neutral" />
            </div>
            <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
              <Button>Primary Action</Button>
              <Button variant="outline">Secondary</Button>
            </div>
          </div>
        </Card>
      </section>

      <section style={{ marginTop: 32 }}>
        <Card>
          <div className="empty-state">
            <div className="icon">🔍</div>
            <div style={{ fontWeight: 600, color: 'var(--color-text-primary)' }}>
              No verifications yet
            </div>
            <div style={{ fontSize: 13 }}>
              Scan a QR to begin a verification flow.
            </div>
          </div>
        </Card>
      </section>
    </main>
  );
}
