'use client';

import { useState } from 'react';
import { Badge } from '../../components/badge';
import { Button } from '../../components/button';
import { Card } from '../../components/card';
import { Topbar } from '../../components/layout/topbar';

export default function SettingsPage() {
  const [name, setName] = useState('Verifier Demo');
  const [email] = useState('verifier@example.com');
  const [organization, setOrganization] = useState('Demo Bank');
  const [timezone, setTimezone] = useState('Asia/Jakarta');
  const [emailNotifications, setEmailNotifications] = useState(true);
  const [slackNotifications, setSlackNotifications] = useState(false);
  const [autoAssign, setAutoAssign] = useState(true);
  const [saved, setSaved] = useState(false);

  function handleSave() {
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  }

  return (
    <>
      <Topbar
        title="Settings"
        subtitle="Manage your verifier account and organization preferences."
      />

      <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 16, alignItems: 'start' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <Card>
            <div className="section-header">
              <h3>Profile</h3>
              <Badge label="Active" kind="success" />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 12, marginTop: 8 }}>
              <div>
                <label className="label">Full Name</label>
                <input
                  className="input"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                />
              </div>
              <div>
                <label className="label">Email</label>
                <input className="input" value={email} disabled style={{ opacity: 0.6 }} />
              </div>
              <div>
                <label className="label">Organization</label>
                <input
                  className="input"
                  value={organization}
                  onChange={(e) => setOrganization(e.target.value)}
                />
              </div>
              <div>
                <label className="label">Role</label>
                <input className="input" value="Compliance Officer" disabled style={{ opacity: 0.6 }} />
              </div>
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Notifications</h3>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: 8 }}>
              <ToggleRow
                label="Email Notifications"
                subtitle="Receive email when a verification is assigned."
                checked={emailNotifications}
                onChange={setEmailNotifications}
              />
              <ToggleRow
                label="Slack Integration"
                subtitle="Send verification updates to your Slack channel."
                checked={slackNotifications}
                onChange={setSlackNotifications}
              />
              <ToggleRow
                label="Auto-Assign Queue"
                subtitle="Automatically assign new pending proofs to you."
                checked={autoAssign}
                onChange={setAutoAssign}
              />
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Regional</h3>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: 12, marginTop: 8 }}>
              <div>
                <label className="label">Timezone</label>
                <select
                  className="input"
                  value={timezone}
                  onChange={(e) => setTimezone(e.target.value)}
                >
                  <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                  <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                  <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                  <option value="UTC">UTC</option>
                </select>
              </div>
              <div>
                <label className="label">Language</label>
                <select className="input" defaultValue="en">
                  <option value="en">English</option>
                  <option value="id">Bahasa Indonesia</option>
                  <option value="ar">العربية</option>
                </select>
              </div>
            </div>
          </Card>
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 16, position: 'sticky', top: 24 }}>
          <Card>
            <div className="section-header">
              <h3>Account</h3>
            </div>

            <div style={{ marginTop: 8, display: 'flex', flexDirection: 'column', gap: 10 }}>
              <div style={{
                display: 'flex',
                alignItems: 'center',
                gap: 12,
                padding: 12,
                backgroundColor: 'var(--color-surface-muted)',
                borderRadius: 'var(--radius-md)',
              }}>
                <div style={{
                  width: 40,
                  height: 40,
                  borderRadius: '50%',
                  backgroundColor: 'var(--color-surface)',
                  border: '1px solid var(--color-border)',
                  display: 'inline-flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontWeight: 700,
                }}>
                  V
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontWeight: 600, fontSize: 13 }}>{name}</div>
                  <div className="mono" style={{ fontSize: 11, color: 'var(--color-text-secondary)' }}>
                    {email}
                  </div>
                </div>
              </div>

              <div style={{ fontSize: 12, color: 'var(--color-text-secondary)', lineHeight: 1.6 }}>
                Member since <strong>Aug 2026</strong> · Last login <strong>today</strong>
              </div>

              <Button variant="outline">Change Password</Button>
              <Button variant="outline">Manage API Keys</Button>
              <Button variant="outline">Two-Factor Auth</Button>
            </div>
          </Card>

          <Card>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              <Button onClick={handleSave}>
                {saved ? '✓ Saved' : 'Save Changes'}
              </Button>
              <button
                className="btn"
                type="button"
                style={{
                  backgroundColor: 'transparent',
                  color: 'var(--color-error)',
                  border: '1px solid var(--color-error)',
                  fontFamily: 'var(--font-sans)',
                }}
              >
                Sign Out
              </button>
            </div>
          </Card>
        </div>
      </div>
    </>
  );
}

function ToggleRow({
  label,
  subtitle,
  checked,
  onChange,
}: {
  label: string;
  subtitle: string;
  checked: boolean;
  onChange: (v: boolean) => void;
}) {
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      gap: 12,
      padding: '10px 12px',
      backgroundColor: 'var(--color-surface-muted)',
      borderRadius: 'var(--radius-md)',
    }}>
      <div style={{ flex: 1 }}>
        <div style={{ fontWeight: 500, fontSize: 13 }}>{label}</div>
        <div style={{ fontSize: 12, color: 'var(--color-text-secondary)', marginTop: 2 }}>
          {subtitle}
        </div>
      </div>
      <label style={{ position: 'relative', display: 'inline-block', width: 40, height: 22 }}>
        <input
          type="checkbox"
          checked={checked}
          onChange={(e) => onChange(e.target.checked)}
          style={{ opacity: 0, width: 0, height: 0 }}
        />
        <span style={{
          position: 'absolute',
          cursor: 'pointer',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: checked ? 'var(--color-primary)' : 'var(--color-border)',
          borderRadius: 22,
          transition: 'background-color 0.2s ease',
        }} />
        <span style={{
          position: 'absolute',
          top: 2,
          left: checked ? 20 : 2,
          width: 18,
          height: 18,
          backgroundColor: '#fff',
          borderRadius: '50%',
          transition: 'left 0.2s ease',
          boxShadow: '0 1px 2px rgba(0,0,0,0.1)',
        }} />
      </label>
    </div>
  );
}
