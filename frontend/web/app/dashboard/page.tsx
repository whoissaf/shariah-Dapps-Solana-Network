import { Badge } from '../components/badge';
import { Button } from '../components/button';
import { Card } from '../components/card';
import { Topbar } from '../components/layout/topbar';

const stats = [
  { label: 'Pending', value: 12, kind: 'info' as const, delta: '+3 today' },
  { label: 'Verified', value: 284, kind: 'success' as const, delta: '+18 this week' },
  { label: 'Rejected', value: 7, kind: 'error' as const, delta: '-2 vs last week' },
  { label: 'Today', value: 9, kind: 'warning' as const, delta: 'In progress' },
];

const recentVerifications = [
  {
    id: '#VRF-2048',
    applicant: 'Anonymous Identity',
    claim: 'Income Threshold',
    status: 'Verified',
    kind: 'success' as const,
    tx: '0x742d35Cc...f0bEb1',
    date: '2 min ago',
  },
  {
    id: '#VRF-2047',
    applicant: 'Anonymous Identity',
    claim: 'Business Category Halal',
    status: 'Pending',
    kind: 'info' as const,
    tx: '—',
    date: '14 min ago',
  },
  {
    id: '#VRF-2046',
    applicant: 'Anonymous Identity',
    claim: 'Age Minimum',
    status: 'Rejected',
    kind: 'error' as const,
    tx: '—',
    date: '1 hour ago',
  },
  {
    id: '#VRF-2045',
    applicant: 'Anonymous Identity',
    claim: 'No Restricted Financing',
    status: 'Verified',
    kind: 'success' as const,
    tx: '0x943a12Fb...8c21d4',
    date: '3 hours ago',
  },
];

const claimDistribution = [
  { label: 'Income', count: 142, percent: 52 },
  { label: 'Age', count: 68, percent: 25 },
  { label: 'Business', count: 43, percent: 16 },
  { label: 'Financing', count: 19, percent: 7 },
];

export default function DashboardPage() {
  return (
    <>
      <Topbar
        title="Dashboard"
        subtitle="Overview of your verification activity."
        actions={
          <>
            <Button variant="outline">Export Report</Button>
            <Button>Scan QR</Button>
          </>
        }
      />

      <section className="bento-grid">
        {stats.map((stat) => (
          <Card key={stat.label}>
            <div style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
            }}>
              <span style={{
                fontSize: 12,
                fontWeight: 600,
                textTransform: 'uppercase',
                letterSpacing: '0.5px',
                color: 'var(--color-text-secondary)',
              }}>
                {stat.label}
              </span>
              <Badge label={stat.label} kind={stat.kind} />
            </div>
            <div className="stat-value">{stat.value}</div>
            <div className="stat-delta">{stat.delta}</div>
          </Card>
        ))}
      </section>

      <section style={{
        display: 'grid',
        gridTemplateColumns: '2fr 1fr',
        gap: 16,
        marginTop: 16,
      }}>
        <Card>
          <div className="section-header">
            <h3>Verification Trend</h3>
            <Badge label="Last 14 days" kind="neutral" />
          </div>
          <div className="chart-placeholder">
            {[38, 52, 44, 61, 58, 72, 66, 81, 74, 89, 95, 88, 102, 97].map((h, i) => (
              <div
                key={i}
                className="chart-bar"
                style={{ height: `${(h / 110) * 100}%` }}
                title={`${h} verifications`}
              />
            ))}
          </div>
          <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            fontSize: 11,
            color: 'var(--color-text-muted)',
            marginTop: 8,
          }}>
            <span>2 weeks ago</span>
            <span>Today</span>
          </div>
        </Card>

        <Card>
          <div className="section-header">
            <h3>Claim Types</h3>
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
            {claimDistribution.map((c) => (
              <div key={c.label}>
                <div style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  fontSize: 13,
                  marginBottom: 6,
                }}>
                  <span style={{ fontWeight: 500 }}>{c.label}</span>
                  <span style={{ color: 'var(--color-text-secondary)' }}>
                    {c.count}
                  </span>
                </div>
                <div style={{
                  height: 6,
                  backgroundColor: 'var(--color-surface-muted)',
                  borderRadius: 'var(--radius-pill)',
                  overflow: 'hidden',
                }}>
                  <div style={{
                    height: '100%',
                    width: `${c.percent}%`,
                    backgroundColor: 'var(--color-primary)',
                    borderRadius: 'var(--radius-pill)',
                  }} />
                </div>
              </div>
            ))}
          </div>
        </Card>
      </section>

      <section style={{ marginTop: 16 }}>
        <Card>
          <div className="section-header">
            <h3>Recent Verifications</h3>
            <Button variant="outline">View All</Button>
          </div>

          <div style={{ overflowX: 'auto' }}>
            <table className="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Applicant</th>
                  <th>Claim</th>
                  <th>Status</th>
                  <th>Blockchain TX</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                {recentVerifications.map((v) => (
                  <tr key={v.id}>
                    <td style={{ fontWeight: 600, fontFamily: 'var(--font-mono)' }}>
                      {v.id}
                    </td>
                    <td>{v.applicant}</td>
                    <td>{v.claim}</td>
                    <td><Badge label={v.status} kind={v.kind} /></td>
                    <td className="mono">{v.tx}</td>
                    <td style={{ color: 'var(--color-text-secondary)' }}>{v.date}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>
      </section>
    </>
  );
}
