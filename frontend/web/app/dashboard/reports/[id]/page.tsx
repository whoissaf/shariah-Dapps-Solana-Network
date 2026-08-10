import Link from 'next/link';
import { Badge } from '../../../components/badge';
import { Button } from '../../../components/button';
import { Card } from '../../../components/card';
import { Topbar } from '../../../components/layout/topbar';

export default function ReportDetailPage({ params }: { params: { id: string } }) {
  const id = params.id;

  const sampleRows = [
    { verification_id: 2048, proof_id: 101, claim_type: 'Income Threshold', verification_status: 'verified', verifier_email: 'verifier@example.com', verified_at: '2026-08-10T10:24:18Z', ai_recommendation: 'approve' },
    { verification_id: 2047, proof_id: 102, claim_type: 'Business Category Halal', verification_status: 'verified', verifier_email: 'verifier@example.com', verified_at: '2026-08-10T09:12:45Z', ai_recommendation: 'approve' },
    { verification_id: 2046, proof_id: 103, claim_type: 'Age Minimum', verification_status: 'rejected', verifier_email: 'verifier@example.com', verified_at: '2026-08-10T08:45:22Z', ai_recommendation: 'reject' },
    { verification_id: 2045, proof_id: 104, claim_type: 'No Restricted Financing', verification_status: 'verified', verifier_email: 'auditor@example.com', verified_at: '2026-08-09T16:32:11Z', ai_recommendation: 'approve' },
    { verification_id: 2044, proof_id: 105, claim_type: 'Income Threshold', verification_status: 'verified', verifier_email: 'verifier@example.com', verified_at: '2026-08-09T14:28:07Z', ai_recommendation: 'approve' },
  ];

  return (
    <>
      <Topbar
        title={`Report ${id}`}
        subtitle="Preview and download the exported verification data."
        actions={
          <Link href="/dashboard/reports">
            <Button variant="outline">← Back to Reports</Button>
          </Link>
        }
      />

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 320px', gap: 16, alignItems: 'start' }}>
        <Card>
          <div className="section-header">
            <h3>Data Preview</h3>
            <Badge label={`${sampleRows.length} rows shown`} kind="neutral" />
          </div>

          <div style={{ overflowX: 'auto', marginTop: 8 }}>
            <table className="data-table">
              <thead>
                <tr>
                  <th>Verification ID</th>
                  <th>Proof ID</th>
                  <th>Claim Type</th>
                  <th>Status</th>
                  <th>Verifier</th>
                  <th>AI</th>
                </tr>
              </thead>
              <tbody>
                {sampleRows.map((row) => (
                  <tr key={row.verification_id}>
                    <td className="mono" style={{ fontWeight: 600 }}>
                      VRF-{row.verification_id}
                    </td>
                    <td className="mono">{row.proof_id}</td>
                    <td>{row.claim_type}</td>
                    <td>
                      <Badge
                        label={row.verification_status}
                        kind={row.verification_status === 'verified' ? 'success' : 'error'}
                      />
                    </td>
                    <td style={{ fontSize: 12, color: 'var(--color-text-secondary)' }}>
                      {row.verifier_email}
                    </td>
                    <td>
                      <Badge
                        label={row.ai_recommendation}
                        kind={row.ai_recommendation === 'approve' ? 'success' : 'error'}
                      />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div style={{
            marginTop: 16,
            padding: 12,
            backgroundColor: 'var(--color-surface-muted)',
            borderRadius: 'var(--radius-md)',
            fontSize: 12,
            color: 'var(--color-text-secondary)',
            textAlign: 'center',
          }}>
            Showing {sampleRows.length} of 284 rows · Download CSV for full data
          </div>
        </Card>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <Card>
            <div className="section-header">
              <h3>Report Info</h3>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12, marginTop: 8 }}>
              <MetaRow label="Report ID" value={id} mono />
              <MetaRow label="Format" value="CSV" />
              <MetaRow label="Rows" value="284" mono />
              <MetaRow label="File Size" value="87 KB" />
              <MetaRow label="Generated" value="Aug 10, 2026" />
              <MetaRow label="Filters" value="Verified · Aug 2026" />
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Actions</h3>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginTop: 8 }}>
              <Button>⬇ Download CSV</Button>
              <Button variant="outline">Share Report Link</Button>
              <Button variant="outline">Regenerate</Button>
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>CSV Schema</h3>
            </div>
            <div style={{
              fontFamily: 'var(--font-mono)',
              fontSize: 11,
              color: 'var(--color-text-secondary)',
              lineHeight: 1.8,
              marginTop: 8,
            }}>
              verification_id<br />
              proof_id<br />
              claim_type<br />
              verification_status<br />
              reject_reason<br />
              verifier_email<br />
              verified_at<br />
              blockchain_tx_hash<br />
              blockchain_network<br />
              blockchain_status<br />
              ai_recommendation
            </div>
          </Card>
        </div>
      </div>
    </>
  );
}

function MetaRow({ label, value, mono }: { label: string; value: string; mono?: boolean }) {
  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8 }}>
      <span style={{ fontSize: 12, color: 'var(--color-text-secondary)' }}>{label}</span>
      <span
        className={mono ? 'mono' : ''}
        style={{ fontSize: 13, fontWeight: 500, color: 'var(--color-text-primary)' }}
      >
        {value}
      </span>
    </div>
  );
}
