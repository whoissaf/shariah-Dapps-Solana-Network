import Link from 'next/link';
import { Badge } from '../../components/badge';
import { Button } from '../../components/button';
import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';
import { DataTable, type Column } from '../../components/ui/data-table';

type TxRecord = {
  id: string;
  txHash: string;
  blockNumber: number;
  network: string;
  contractAddress: string;
  eventName: string;
  status: 'Confirmed' | 'Pending' | 'Failed';
  timestamp: string;
};

const transactions: TxRecord[] = [
  {
    id: '1',
    txHash: '0x943a12fb7a2c8e41d9b6f3e0c5b67a1d8f2345c6b789',
    blockNumber: 19847231,
    network: 'Ethereum Sepolia',
    contractAddress: '0x5fbdb2315678afecb367f032d93f642f64180aa3',
    eventName: 'ProofStored',
    status: 'Confirmed',
    timestamp: '10 min ago',
  },
  {
    id: '2',
    txHash: '0x5fbdb2315678afecb367f032d93f642f64180aa3',
    blockNumber: 19847198,
    network: 'Ethereum Sepolia',
    contractAddress: '0x5fbdb2315678afecb367f032d93f642f64180aa3',
    eventName: 'ProofStored',
    status: 'Confirmed',
    timestamp: '2 hours ago',
  },
  {
    id: '3',
    txHash: '0x6789abcd0123456789abcdef0123456789abcdef01',
    blockNumber: 19847145,
    network: 'Ethereum Sepolia',
    contractAddress: '0x5fbdb2315678afecb367f032d93f642f64180aa3',
    eventName: 'VerificationFinalized',
    status: 'Confirmed',
    timestamp: '5 hours ago',
  },
  {
    id: '4',
    txHash: '0x1234567890abcdef1234567890abcdef12345678',
    blockNumber: 19847082,
    network: 'Ethereum Sepolia',
    contractAddress: '0x5fbdb2315678afecb367f032d93f642f64180aa3',
    eventName: 'ProofStored',
    status: 'Confirmed',
    timestamp: '1 day ago',
  },
  {
    id: '5',
    txHash: '0x0abcdef0123456789abcdef0123456789abcdef',
    blockNumber: 19846951,
    network: 'Ethereum Sepolia',
    contractAddress: '0x5fbdb2315678afecb367f032d93f642f64180aa3',
    eventName: 'IdentityCommitted',
    status: 'Confirmed',
    timestamp: '2 days ago',
  },
];

const columns: Column<TxRecord>[] = [
  {
    key: 'txHash',
    label: 'TX Hash',
    render: (row) => (
      <Link
        href={`/dashboard/blockchain/${row.txHash}`}
        className="mono"
        style={{
          fontWeight: 600,
          color: 'var(--color-primary-dark)',
          display: 'inline-flex',
          alignItems: 'center',
          gap: 4,
        }}
      >
        <span>{row.txHash.substring(0, 10)}...{row.txHash.substring(row.txHash.length - 8)}</span>
        <span style={{ fontSize: 10 }}>↗</span>
      </Link>
    ),
  },
  {
    key: 'eventName',
    label: 'Event',
    render: (row) => (
      <Badge label={row.eventName} kind="neutral" />
    ),
  },
  {
    key: 'blockNumber',
    label: 'Block',
    render: (row) => (
      <span className="mono" style={{ fontSize: 12 }}>
        #{row.blockNumber.toLocaleString()}
      </span>
    ),
  },
  {
    key: 'status',
    label: 'Status',
    render: (row) => (
      <Badge
        label={row.status}
        kind={row.status === 'Confirmed' ? 'success' : row.status === 'Failed' ? 'error' : 'warning'}
      />
    ),
  },
  {
    key: 'network',
    label: 'Network',
  },
  {
    key: 'timestamp',
    label: 'Timestamp',
    render: (row) => (
      <span style={{ color: 'var(--color-text-secondary)', fontSize: 12 }}>
        {row.timestamp}
      </span>
    ),
  },
];

export default function BlockchainExplorerPage() {
  return (
    <>
      <Topbar
        title="Blockchain Explorer"
        subtitle="All on-chain transactions anchored to Ethereum Sepolia testnet."
        actions={
          <Button variant="outline">View on Etherscan ↗</Button>
        }
      />

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16, marginBottom: 16 }}>
        <Card>
          <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-text-secondary)', textTransform: 'uppercase', letterSpacing: 0.5 }}>
            Contract
          </div>
          <div className="mono" style={{ fontSize: 13, marginTop: 6, wordBreak: 'break-all' }}>
            0x5fbdb2...180aa3
          </div>
          <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 4 }}>
            Semaphore Verifier
          </div>
        </Card>
        <Card>
          <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-text-secondary)', textTransform: 'uppercase', letterSpacing: 0.5 }}>
            Network
          </div>
          <div style={{ fontSize: 16, fontWeight: 600, marginTop: 6 }}>
            Ethereum Sepolia
          </div>
          <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 4 }}>
            Chain ID: 11155111
          </div>
        </Card>
        <Card>
          <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-text-secondary)', textTransform: 'uppercase', letterSpacing: 0.5 }}>
            Total TX
          </div>
          <div style={{ fontSize: 28, fontWeight: 700, marginTop: 6, color: 'var(--color-primary-dark)' }}>
            {transactions.length}
          </div>
          <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 4 }}>
            Last 30 days
          </div>
        </Card>
      </div>

      <Card>
        <div className="section-header">
          <h3>Recent Transactions</h3>
          <Badge label={`${transactions.length} total`} kind="neutral" />
        </div>
        <DataTable columns={columns} rows={transactions} />
      </Card>
    </>
  );
}
