'use client';

import { useState } from 'react';
import Link from 'next/link';
import { Badge } from '../../components/badge';
import { Button } from '../../components/button';
import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';
import { DataTable, type Column } from '../../components/ui/data-table';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

type Report = {
  id: string;
  name: string;
  type: string;
  filters: string;
  generatedAt: string;
  rowCount: number;
  status: 'Completed' | 'Processing' | 'Failed';
};

const reports: Report[] = [
  {
    id: 'RPT-2024-001',
    name: 'Monthly Verification Summary',
    type: 'CSV',
    filters: 'All statuses · Aug 2026',
    generatedAt: 'Today, 09:15',
    rowCount: 284,
    status: 'Completed',
  },
  {
    id: 'RPT-2024-002',
    name: 'Rejected Claims Analysis',
    type: 'CSV',
    filters: 'Rejected only · Last 30 days',
    generatedAt: 'Yesterday, 14:30',
    rowCount: 7,
    status: 'Completed',
  },
  {
    id: 'RPT-2024-003',
    name: 'Income Threshold Compliance',
    type: 'CSV',
    filters: 'Income claims · Aug 2026',
    generatedAt: '2 days ago',
    rowCount: 142,
    status: 'Completed',
  },
  {
    id: 'RPT-2024-004',
    name: 'Quarterly Sharia Audit',
    type: 'CSV',
    filters: 'All halal claims · Q3 2026',
    generatedAt: '1 week ago',
    rowCount: 512,
    status: 'Completed',
  },
];

const columns: Column<Report>[] = [
  {
    key: 'id',
    label: 'Report ID',
    render: (row) => (
      <Link
        href={`/dashboard/reports/${row.id}`}
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
  { key: 'name', label: 'Name' },
  {
    key: 'type',
    label: 'Format',
    render: (row) => <Badge label={row.type} kind="neutral" />,
  },
  {
    key: 'filters',
    label: 'Filters',
    render: (row) => (
      <span style={{ color: 'var(--color-text-secondary)', fontSize: 12 }}>
        {row.filters}
      </span>
    ),
  },
  {
    key: 'rowCount',
    label: 'Rows',
    render: (row) => (
      <span className="mono" style={{ fontWeight: 600 }}>
        {row.rowCount}
      </span>
    ),
  },
  {
    key: 'status',
    label: 'Status',
    render: (row) => (
      <Badge
        label={row.status}
        kind={row.status === 'Completed' ? 'success' : row.status === 'Failed' ? 'error' : 'warning'}
      />
    ),
  },
  {
    key: 'generatedAt',
    label: 'Generated',
    render: (row) => (
      <span style={{ color: 'var(--color-text-secondary)', fontSize: 12 }}>
        {row.generatedAt}
      </span>
    ),
  },
];

export default function ReportsPage() {
  const [exporting, setExporting] = useState(false);

  function handleExportCsv() {
    setExporting(true);
    const token = 'demo-token-replace-with-real-token';
    const url = `${API_BASE}/api/report/export?status=verified`;
    const a = document.createElement('a');
    a.href = url;
    a.setAttribute('download', 'verification-report.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => setExporting(false), 1500);
  }

  return (
    <>
      <Topbar
        title="Reports"
        subtitle="Export compliance reports for audit and archival."
        actions={
          <>
            <Button variant="outline">Schedule Report</Button>
            <Button onClick={handleExportCsv}>
              {exporting ? 'Exporting...' : 'Export New CSV'}
            </Button>
          </>
        }
      />

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16, marginBottom: 16 }}>
        <Card>
          <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-text-secondary)', textTransform: 'uppercase', letterSpacing: 0.5 }}>
            Total Reports
          </div>
          <div style={{ fontSize: 28, fontWeight: 700, color: 'var(--color-primary-dark)', marginTop: 6 }}>
            {reports.length}
          </div>
          <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 4 }}>
            Last 30 days
          </div>
        </Card>
        <Card>
          <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-text-secondary)', textTransform: 'uppercase', letterSpacing: 0.5 }}>
            Records Exported
          </div>
          <div style={{ fontSize: 28, fontWeight: 700, color: 'var(--color-primary-dark)', marginTop: 6 }}>
            {reports.reduce((acc, r) => acc + r.rowCount, 0).toLocaleString()}
          </div>
          <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 4 }}>
            Across all reports
          </div>
        </Card>
        <Card>
          <div style={{ fontSize: 11, fontWeight: 600, color: 'var(--color-text-secondary)', textTransform: 'uppercase', letterSpacing: 0.5 }}>
            Format
          </div>
          <div style={{ fontSize: 18, fontWeight: 600, marginTop: 10 }}>
            CSV
          </div>
          <div style={{ fontSize: 11, color: 'var(--color-text-muted)', marginTop: 4 }}>
            PDF coming soon
          </div>
        </Card>
      </div>

      <Card>
        <div className="section-header">
          <h3>Report Library</h3>
          <Badge label={`${reports.length} reports`} kind="neutral" />
        </div>
        <DataTable columns={columns} rows={reports} />
      </Card>
    </>
  );
}
