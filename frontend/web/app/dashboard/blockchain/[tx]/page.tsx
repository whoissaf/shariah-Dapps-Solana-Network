import Link from 'next/link';
import { Badge } from '../../../components/badge';
import { Button } from '../../../components/button';
import { Card } from '../../../components/card';
import { Topbar } from '../../../components/layout/topbar';

export default function BlockchainTxPage({ params }: { params: { tx: string } }) {
  const txHash = params.tx;
  const etherscanUrl = `https://sepolia.etherscan.io/tx/${txHash}`;

  return (
    <>
      <Topbar
        title="Transaction Detail"
        subtitle="On-chain verification of the stored proof hash."
        actions={
          <Link href="/dashboard/blockchain">
            <Button variant="outline">← Back to Explorer</Button>
          </Link>
        }
      />

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 16, alignItems: 'start' }}>
        <Card>
          <div className="section-header">
            <h3>Transaction</h3>
            <Badge label="Confirmed" kind="success" />
          </div>

          <div style={{
            padding: 16,
            backgroundColor: 'var(--color-surface-muted)',
            borderRadius: 'var(--radius-md)',
            marginTop: 8,
          }}>
            <div className="label" style={{ marginBottom: 6 }}>TX Hash</div>
            <div className="mono" style={{ fontSize: 14, wordBreak: 'break-all', fontWeight: 600 }}>
              {txHash}
            </div>
          </div>

          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(2, 1fr)',
            gap: 16,
            marginTop: 16,
          }}>
            <InfoBlock label="Block Number" value="19,847,231" mono />
            <InfoBlock label="Confirmations" value="142" />
            <InfoBlock label="Gas Used" value="187,421 wei" mono />
            <InfoBlock label="Gas Price" value="2.4 gwei" mono />
            <InfoBlock label="Timestamp" value="2026-08-10 10:24:18 UTC" />
            <InfoBlock label="Status" value="Success" />
          </div>

          <a
            href={etherscanUrl}
            target="_blank"
            rel="noopener noreferrer"
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: 6,
              marginTop: 20,
              padding: '10px 16px',
              backgroundColor: 'var(--color-primary)',
              color: '#fff',
              borderRadius: 'var(--radius-md)',
              fontWeight: 600,
              fontSize: 14,
            }}
          >
            View on Etherscan ↗
          </a>
        </Card>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <Card>
            <div className="section-header">
              <h3>Event Log</h3>
            </div>
            <div style={{ marginTop: 8, display: 'flex', flexDirection: 'column', gap: 8 }}>
              <div style={{
                padding: 12,
                backgroundColor: 'rgba(16, 185, 129, 0.04)',
                borderRadius: 'var(--radius-md)',
                border: '1px solid rgba(16, 185, 129, 0.15)',
              }}>
                <Badge label="ProofStored" kind="success" />
                <div className="mono" style={{ marginTop: 8, fontSize: 11 }}>
                  topic0: 0x8c5be1e5eb...
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Contract</h3>
            </div>
            <div style={{ marginTop: 8 }}>
              <div className="label">Address</div>
              <div className="mono" style={{ fontSize: 12, wordBreak: 'break-all', marginTop: 4 }}>
                0x5fbdb2315678afecb367f032d93f642f64180aa3
              </div>
              <div style={{ marginTop: 12 }}>
                <div className="label">Name</div>
                <div style={{ fontSize: 13, fontWeight: 500, marginTop: 4 }}>
                  Semaphore Verifier v1.0
                </div>
              </div>
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Input Data</h3>
            </div>
            <div style={{
              padding: 12,
              backgroundColor: 'var(--color-surface-muted)',
              borderRadius: 'var(--radius-md)',
              fontFamily: 'var(--font-mono)',
              fontSize: 11,
              color: 'var(--color-text-secondary)',
              wordBreak: 'break-all',
              lineHeight: 1.6,
              marginTop: 8,
            }}>
              0x3d18b912
              <br />
              0000000000000000000000000000000000000000000000000000000000000742
              <br />
              742d35cc6634c0532925a3b844bc9e7595f0beb1e9e0c5b67a1d8f2345c6b789
            </div>
          </Card>
        </div>
      </div>
    </>
  );
}

function InfoBlock({ label, value, mono }: { label: string; value: string; mono?: boolean }) {
  return (
    <div>
      <div className="label" style={{ marginBottom: 4 }}>{label}</div>
      <div
        className={mono ? 'mono' : ''}
        style={{
          fontSize: 14,
          fontWeight: 500,
          color: 'var(--color-text-primary)',
        }}
      >
        {value}
      </div>
    </div>
  );
}
