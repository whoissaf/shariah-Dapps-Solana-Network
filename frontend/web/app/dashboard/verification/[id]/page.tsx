'use client';

import { useState } from 'react';
import Link from 'next/link';
import { Badge } from '../../../components/badge';
import { Button } from '../../../components/button';
import { Card } from '../../../components/card';
import { Topbar } from '../../../components/layout/topbar';

export default function VerificationDetailPage({ params }: { params: { id: string } }) {
  const id = params.id;
  const [decision, setDecision] = useState<'pending' | 'verified' | 'rejected'>('pending');
  const [rejectReason, setRejectReason] = useState('');
  const [loading, setLoading] = useState(false);

  function handleApprove() {
    setLoading(true);
    setTimeout(() => {
      setDecision('verified');
      setLoading(false);
    }, 600);
  }

  function handleReject() {
    if (!rejectReason.trim() || rejectReason.length < 5) {
      alert('Reject reason must be at least 5 characters.');
      return;
    }
    setLoading(true);
    setTimeout(() => {
      setDecision('rejected');
      setLoading(false);
    }, 600);
  }

  const isFinal = decision !== 'pending';

  return (
    <>
      <Topbar
        title={`Verification #${id}`}
        subtitle="Detailed view of applicant identity and proof."
        actions={
          <Link href="/dashboard/verification">
            <Button variant="outline">← Back to Queue</Button>
          </Link>
        }
      />

      <div style={{
        display: 'grid',
        gridTemplateColumns: '1fr 380px',
        gap: 16,
        alignItems: 'start',
      }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <Card>
            <div className="section-header">
              <h3>Applicant</h3>
              <Badge label="Anonymous" kind="info" />
            </div>

            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(2, 1fr)',
              gap: 16,
              marginTop: 8,
            }}>
              <InfoBlock label="Anonymous ID" value="a3b4c5d6-e7f8-9012" mono />
              <InfoBlock label="Wallet Address" value="0x742d35Cc...f0bEb1" mono />
              <InfoBlock label="Identity Commitment" value="0x943a12fb...8c21d4" mono />
              <InfoBlock label="Submitted" value="2 minutes ago" />
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Proof Information</h3>
              <Badge label="Income Threshold" kind="neutral" />
            </div>

            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(2, 1fr)',
              gap: 16,
              marginTop: 8,
            }}>
              <InfoBlock label="Proof Hash" value="0x742d35cc6634c0532925a3b8..." mono />
              <InfoBlock label="Blockchain TX" value="0x943a12fb...8c21d4" mono />
              <InfoBlock label="Circuit" value="Semaphore + Groth16" />
              <InfoBlock label="Block Number" value="19,847,231" />
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Verification Checks</h3>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: 8 }}>
              <CheckRow label="QR Signature Valid" passed />
              <CheckRow label="Identity Commitment Match" passed />
              <CheckRow label="Rule: Income Threshold (≥ 5,000,000)" passed detail="IDR 7,000,000" />
              <CheckRow label="Rule: Active Restricted Financing" passed detail="None detected" />
              <CheckRow label="Blockchain Anchor Confirmed" passed />
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Supporting Documents</h3>
              <Badge label="2 files" kind="neutral" />
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginTop: 8 }}>
              <DocRow name="salary_slip_july.pdf" size="842 KB" type="Salary" />
              <DocRow name="employment_letter.pdf" size="1.2 MB" type="Salary" />
            </div>
          </Card>
        </div>

        <Card>
          <div className="section-header">
            <h3>Decision</h3>
            {decision === 'verified' && <Badge label="Verified" kind="success" />}
            {decision === 'rejected' && <Badge label="Rejected" kind="error" />}
            {decision === 'pending' && <Badge label="Pending" kind="info" />}
          </div>

          <div style={{
            padding: 16,
            backgroundColor: 'var(--color-surface-muted)',
            borderRadius: 'var(--radius-md)',
            marginBottom: 16,
          }}>
            <div style={{ fontSize: 12, color: 'var(--color-text-secondary)', marginBottom: 4 }}>
              AI Recommendation
            </div>
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: 8,
              marginBottom: 8,
            }}>
              <Badge label="Approve" kind="success" />
              <span style={{ fontSize: 12, color: 'var(--color-text-secondary)' }}>
                Confidence 94%
              </span>
            </div>
            <div style={{ fontSize: 13, color: 'var(--color-text-primary)', lineHeight: 1.5 }}>
              All technical checks passed. Income threshold satisfied with supporting documents. Recommend approval.
            </div>
            <Link href={`/dashboard/ai/${id}`} style={{
              display: 'inline-block',
              marginTop: 10,
              fontSize: 12,
              color: 'var(--color-primary-dark)',
              fontWeight: 600,
            }}>
              View full AI explanation →
            </Link>
          </div>

          {decision === 'pending' && (
            <>
              <label className="label" style={{ marginBottom: 4 }}>
                Reject Reason (if rejecting)
              </label>
              <textarea
                className="input"
                value={rejectReason}
                onChange={(e) => setRejectReason(e.target.value)}
                placeholder="Provide at least 5 characters of explanation..."
                rows={4}
                style={{
                  resize: 'vertical',
                  marginBottom: 16,
                  fontFamily: 'var(--font-sans)',
                }}
              />

              <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                <Button onClick={handleApprove}>
                  {loading ? 'Processing...' : '✓ Approve Verification'}
                </Button>
                <button
                  className="btn"
                  onClick={handleReject}
                  type="button"
                  disabled={loading}
                  style={{
                    backgroundColor: 'transparent',
                    color: 'var(--color-error)',
                    border: '1px solid var(--color-error)',
                    opacity: loading ? 0.6 : 1,
                  }}
                >
                  ✕ Reject Verification
                </button>
              </div>
            </>
          )}

          {decision === 'verified' && (
            <div style={{
              padding: 16,
              backgroundColor: 'rgba(16, 185, 129, 0.08)',
              borderRadius: 'var(--radius-md)',
              border: '1px solid rgba(16, 185, 129, 0.2)',
            }}>
              <div style={{ fontWeight: 600, color: 'var(--color-success)', marginBottom: 4 }}>
                ✓ Verification Approved
              </div>
              <div style={{ fontSize: 13, color: 'var(--color-text-primary)' }}>
                Decision recorded and audit trail updated. Proof status on-chain will be updated shortly.
              </div>
            </div>
          )}

          {decision === 'rejected' && (
            <div style={{
              padding: 16,
              backgroundColor: 'rgba(239, 68, 68, 0.08)',
              borderRadius: 'var(--radius-md)',
              border: '1px solid rgba(239, 68, 68, 0.2)',
            }}>
              <div style={{ fontWeight: 600, color: 'var(--color-error)', marginBottom: 4 }}>
                ✕ Verification Rejected
              </div>
              <div style={{ fontSize: 13, color: 'var(--color-text-primary)', marginBottom: 6 }}>
                Reason:
              </div>
              <div style={{
                fontSize: 12,
                color: 'var(--color-text-secondary)',
                fontStyle: 'italic',
              }}>
                {rejectReason}
              </div>
            </div>
          )}

          <div style={{ marginTop: 16, paddingTop: 16, borderTop: '1px solid var(--color-border)' }}>
            <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginBottom: 6 }}>
              Verifier
            </div>
            <div style={{ fontWeight: 600, fontSize: 13 }}>Verifier Demo</div>
            <div className="mono" style={{ fontSize: 11, color: 'var(--color-text-secondary)' }}>
              verifier@example.com
            </div>
          </div>
        </Card>
      </div>
    </>
  );
}

function InfoBlock({ label, value, mono }: { label: string; value: string; mono?: boolean }) {
  return (
    <div>
      <div className="label" style={{ marginBottom: 4 }}>{label}</div>
      <div className={mono ? 'mono' : ''} style={{
        fontSize: 13,
        fontWeight: 500,
        color: 'var(--color-text-primary)',
        wordBreak: 'break-all',
      }}>
        {value}
      </div>
    </div>
  );
}

function CheckRow({ label, passed, detail }: { label: string; passed: boolean; detail?: string }) {
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      gap: 10,
      padding: '10px 12px',
      backgroundColor: passed ? 'rgba(16, 185, 129, 0.04)' : 'rgba(239, 68, 68, 0.04)',
      borderRadius: 'var(--radius-md)',
      border: `1px solid ${passed ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)'}`,
    }}>
      <div style={{
        width: 20,
        height: 20,
        borderRadius: '50%',
        backgroundColor: passed ? 'var(--color-success)' : 'var(--color-error)',
        color: '#fff',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: 11,
        fontWeight: 700,
        flexShrink: 0,
      }}>
        {passed ? '✓' : '✕'}
      </div>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 13, fontWeight: 500, color: 'var(--color-text-primary)' }}>
          {label}
        </div>
        {detail && (
          <div style={{ fontSize: 11, color: 'var(--color-text-secondary)', marginTop: 2 }}>
            {detail}
          </div>
        )}
      </div>
      <Badge label={passed ? 'Passed' : 'Failed'} kind={passed ? 'success' : 'error'} />
    </div>
  );
}

function DocRow({ name, size, type }: { name: string; size: string; type: string }) {
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      gap: 12,
      padding: '10px 12px',
      backgroundColor: 'var(--color-surface-muted)',
      borderRadius: 'var(--radius-md)',
      border: '1px solid var(--color-border)',
    }}>
      <div style={{
        width: 32,
        height: 32,
        borderRadius: 8,
        backgroundColor: 'var(--color-surface)',
        border: '1px solid var(--color-border)',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: 'var(--color-error)',
        fontSize: 12,
        fontWeight: 700,
      }}>
        PDF
      </div>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 13, fontWeight: 500 }}>{name}</div>
        <div style={{ fontSize: 11, color: 'var(--color-text-secondary)' }}>
          {type} · {size}
        </div>
      </div>
      <button className="btn btn-outline" type="button" style={{ padding: '6px 10px', fontSize: 12 }}>
        View
      </button>
    </div>
  );
}
