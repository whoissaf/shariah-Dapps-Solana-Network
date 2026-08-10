'use client';

import { useState, useMemo } from 'react';
import Link from 'next/link';
import { Badge } from '../../components/badge';
import { Button } from '../../components/button';
import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';
import { DataTable, type Column } from '../../components/ui/data-table';
import { FilterChips } from '../../components/ui/filter-chips';
import { SearchBar } from '../../components/ui/search-bar';

type Verification = {
  id: string;
  applicant: string;
  wallet: string;
  claimType: string;
  status: 'Pending' | 'Verified' | 'Rejected';
  submittedAt: string;
  blockchainTx?: string;
};

const verifications: Verification[] = [
  {
    id: 'VRF-2048',
    applicant: 'Anonymous #a3b4c5d6',
    wallet: '0x742d...0bEb1',
    claimType: 'Income Threshold',
    status: 'Pending',
    submittedAt: '2 minutes ago',
  },
  {
    id: 'VRF-2047',
    applicant: 'Anonymous #e7f89012',
    wallet: '0x943a...21d4',
    claimType: 'Business Category Halal',
    status: 'Pending',
    submittedAt: '14 minutes ago',
  },
  {
    id: 'VRF-2046',
    applicant: 'Anonymous #c5d6e7f8',
    wallet: '0x5fbd...0aa3',
    claimType: 'Age Minimum',
    status: 'Pending',
    submittedAt: '1 hour ago',
  },
  {
    id: 'VRF-2045',
    applicant: 'Anonymous #9012a3b4',
    wallet: '0x3b4c...6789',
    claimType: 'No Restricted Financing',
    status: 'Verified',
    submittedAt: '3 hours ago',
    blockchainTx: '0x943a12...8c21d4',
  },
  {
    id: 'VRF-2044',
    applicant: 'Anonymous #f8901234',
    wallet: '0x2c3d...4567',
    claimType: 'Income Threshold',
    status: 'Verified',
    submittedAt: '5 hours ago',
    blockchainTx: '0x5fbdb2...180aa3',
  },
  {
    id: 'VRF-2043',
    applicant: 'Anonymous #23456789',
    wallet: '0x8a9b...cdef',
    claimType: 'Business Category Halal',
    status: 'Rejected',
    submittedAt: 'Yesterday',
  },
  {
    id: 'VRF-2042',
    applicant: 'Anonymous #4567890a',
    wallet: '0x1234...5678',
    claimType: 'Age Minimum',
    status: 'Verified',
    submittedAt: '2 days ago',
    blockchainTx: '0x6789...abcd',
  },
  {
    id: 'VRF-2041',
    applicant: 'Anonymous #bcdef012',
    wallet: '0x0abc...def0',
    claimType: 'Income Threshold',
    status: 'Rejected',
    submittedAt: '3 days ago',
  },
];

function statusKind(status: Verification['status']) {
  if (status === 'Verified') return 'success' as const;
  if (status === 'Rejected') return 'error' as const;
  return 'info' as const;
}

export default function VerificationQueuePage() {
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');

  const chips = [
    { id: 'all', label: 'All', count: verifications.length },
    { id: 'pending', label: 'Pending', count: verifications.filter((v) => v.status === 'Pending').length },
    { id: 'verified', label: 'Verified', count: verifications.filter((v) => v.status === 'Verified').length },
    { id: 'rejected', label: 'Rejected', count: verifications.filter((v) => v.status === 'Rejected').length },
  ];

  const rows = useMemo(() => {
    return verifications.filter((v) => {
      if (filter !== 'all' && v.status.toLowerCase() !== filter) return false;
      if (search && !v.applicant.toLowerCase().includes(search.toLowerCase()) && !v.id.toLowerCase().includes(search.toLowerCase())) {
        return false;
      }
      return true;
    });
  }, [filter, search]);

  const columns: Column<Verification>[] = [
    {
      key: 'id',
      label: 'ID',
      render: (row) => (
        <Link
          href={`/dashboard/verification/${row.id}`}
          style={{
            fontFamily: 'var(--font-mono)',
            fontWeight: 600,
            color: 'var(--color-primary-dark)',
          }}
        >
          {row.id}
        </Link>
      ),
    },
    {
      key: 'applicant',
      label: 'Applicant',
      render: (row) => (
        <div>
          <div style={{ fontWeight: 500, color: 'var(--color-text-primary)' }}>
            {row.applicant}
          </div>
          <div className="mono" style={{ marginTop: 2 }}>
            {row.wallet}
          </div>
        </div>
      ),
    },
    {
      key: 'claimType',
      label: 'Claim Type',
    },
    {
      key: 'status',
      label: 'Status',
      render: (row) => <Badge label={row.status} kind={statusKind(row.status)} />,
    },
    {
      key: 'blockchainTx',
      label: 'Blockchain TX',
      render: (row) =>
        row.blockchainTx ? (
          <span className="mono">{row.blockchainTx}</span>
        ) : (
          <span style={{ color: 'var(--color-text-muted)' }}>—</span>
        ),
    },
    {
      key: 'submittedAt',
      label: 'Submitted',
      render: (row) => (
        <span style={{ color: 'var(--color-text-secondary)', fontSize: 12 }}>
          {row.submittedAt}
        </span>
      ),
    },
  ];

  return (
    <>
      <Topbar
        title="Verification Queue"
        subtitle="Review and process incoming zero-knowledge proofs."
        actions={
          <>
            <Button variant="outline">Export</Button>
            <Link href="/dashboard/verification/scan">
              <Button>Scan QR</Button>
            </Link>
          </>
        }
      />

      <Card>
        <div style={{
          display: 'flex',
          gap: 12,
          marginBottom: 16,
          alignItems: 'center',
          flexWrap: 'wrap',
        }}>
          <SearchBar
            value={search}
            onChange={setSearch}
            placeholder="Search by ID or applicant..."
          />
          <div style={{ display: 'flex', gap: 8 }}>
            <Button variant="outline">Filter</Button>
          </div>
        </div>

        <div style={{ marginBottom: 16 }}>
          <FilterChips chips={chips} activeId={filter} onSelect={setFilter} />
        </div>

        <DataTable
          columns={columns}
          rows={rows}
          emptyText="No verifications match your filter."
        />
      </Card>
    </>
  );
}
