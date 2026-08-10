'use client';

import { useState, type FormEvent } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from '../components/button';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('verifier@example.com');
  const [password, setPassword] = useState('password123');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    if (!email || !password) {
      setError('Email and password are required.');
      return;
    }
    setLoading(true);
    setTimeout(() => {
      router.push('/dashboard');
    }, 600);
  }

  return (
    <div className="login-shell">
      <div className="login-hero">
        <div className="login-hero-logo">✓</div>
        <div>
          <h2>Verifier Portal</h2>
          <p>
            Enterprise compliance dashboard for Sharia-compliant zero-knowledge
            proof verification.
          </p>
        </div>

        <div className="login-hero-feature">
          <div className="login-hero-feature-icon">◧</div>
          <div className="login-hero-feature-text">
            Monitor verification queue with real-time status updates.
          </div>
        </div>

        <div className="login-hero-feature">
          <div className="login-hero-feature-icon">✓</div>
          <div className="login-hero-feature-text">
            AI-assisted explanation for transparent compliance decisions.
          </div>
        </div>

        <div className="login-hero-feature">
          <div className="login-hero-feature-icon">⊙</div>
          <div className="login-hero-feature-text">
            Complete audit trail linked to on-chain Ethereum transactions.
          </div>
        </div>
      </div>

      <div className="login-panel">
        <div className="form-title">Sign in</div>
        <div className="form-subtitle">Access your verifier workspace.</div>

        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label className="label" htmlFor="email">Email</label>
            <input
              id="email"
              className="input"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="verifier@example.com"
              autoComplete="email"
            />
          </div>

          <div className="form-group">
            <label className="label" htmlFor="password">Password</label>
            <input
              id="password"
              className="input"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Enter your password"
              autoComplete="current-password"
            />
          </div>

          {error && (
            <div style={{
              padding: '10px 12px',
              backgroundColor: 'rgba(239, 68, 68, 0.1)',
              color: 'var(--color-error)',
              borderRadius: 'var(--radius-md)',
              fontSize: '13px',
              marginBottom: '16px',
            }}>
              {error}
            </div>
          )}

          <Button>{loading ? 'Signing in...' : 'Sign In'}</Button>
        </form>

        <div style={{
          marginTop: 24,
          fontSize: 12,
          color: 'var(--color-text-muted)',
          textAlign: 'center',
        }}>
          Demo credentials pre-filled. Press Sign In.
        </div>
      </div>
    </div>
  );
}
