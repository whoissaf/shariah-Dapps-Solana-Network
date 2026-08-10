'use client';

import { useState, useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { Badge } from '../../../components/badge';
import { Button } from '../../../components/button';
import { Card } from '../../../components/card';
import { Topbar } from '../../../components/layout/topbar';

export default function QrScannerPage() {
  const router = useRouter();
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [status, setStatus] = useState<'idle' | 'scanning' | 'detected'>('idle');
  const [scannedId, setScannedId] = useState('');
  const [manualInput, setManualInput] = useState('');
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const width = canvas.width;
    const height = canvas.height;
    let frameId: number;
    let t = 0;

    function draw() {
      if (!ctx) return;
      t += 0.02;

      ctx.fillStyle = '#0F172A';
      ctx.fillRect(0, 0, width, height);

      for (let i = 0; i < 40; i++) {
        const x = (i * 37 + t * 50) % width;
        const y = (Math.sin(t + i) * height) / 2 + height / 2;
        ctx.fillStyle = 'rgba(16, 185, 129, ' + (0.1 + Math.random() * 0.2) + ')';
        ctx.fillRect(x, y, 2, 2);
      }

      if (status === 'scanning') {
        const scanLineY = (Math.sin(t) * height) / 2 + height / 2;
        ctx.strokeStyle = 'rgba(16, 185, 129, 0.8)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(0, scanLineY);
        ctx.lineTo(width, scanLineY);
        ctx.stroke();

        const glow = ctx.createLinearGradient(0, scanLineY - 30, 0, scanLineY + 30);
        glow.addColorStop(0, 'rgba(16, 185, 129, 0)');
        glow.addColorStop(0.5, 'rgba(16, 185, 129, 0.15)');
        glow.addColorStop(1, 'rgba(16, 185, 129, 0)');
        ctx.fillStyle = glow;
        ctx.fillRect(0, scanLineY - 30, width, 60);
      }

      const corner = 40;
      ctx.strokeStyle = 'rgba(16, 185, 129, 0.9)';
      ctx.lineWidth = 3;
      const cx = width / 2 - 100;
      const cy = height / 2 - 100;
      const size = 200;
      ctx.beginPath();
      ctx.moveTo(cx, cy + corner);
      ctx.lineTo(cx, cy);
      ctx.lineTo(cx + corner, cy);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(cx + size - corner, cy);
      ctx.lineTo(cx + size, cy);
      ctx.lineTo(cx + size, cy + corner);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(cx, cy + size - corner);
      ctx.lineTo(cx, cy + size);
      ctx.lineTo(cx + corner, cy + size);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(cx + size - corner, cy + size);
      ctx.lineTo(cx + size, cy + size);
      ctx.lineTo(cx + size, cy + size - corner);
      ctx.stroke();

      frameId = requestAnimationFrame(draw);
    }

    draw();
    return () => cancelAnimationFrame(frameId);
  }, [status]);

  function startScanning() {
    setStatus('scanning');
    setErrorMsg('');
    setTimeout(() => {
      const randomId = 'VRF-' + (2048 + Math.floor(Math.random() * 10));
      setScannedId(randomId);
      setStatus('detected');
    }, 2500);
  }

  function handleManualSubmit() {
    if (!manualInput.trim()) {
      setErrorMsg('Please enter a valid proof ID.');
      return;
    }
    setScannedId(manualInput.trim());
    setStatus('detected');
    setErrorMsg('');
  }

  function openDetail() {
    router.push('/dashboard/verification/' + scannedId);
  }

  function reset() {
    setStatus('idle');
    setScannedId('');
    setManualInput('');
    setErrorMsg('');
  }

  return (
    <>
      <Topbar
        title="QR Scanner"
        subtitle="Scan a proof QR code from the applicant's device."
      />

      <div style={{
        display: 'grid',
        gridTemplateColumns: '1.3fr 1fr',
        gap: 16,
        alignItems: 'start',
      }}>
        <Card>
          <div className="section-header">
            <h3>Camera View</h3>
            {status === 'idle' && <Badge label="Ready" kind="neutral" />}
            {status === 'scanning' && <Badge label="Scanning..." kind="info" />}
            {status === 'detected' && <Badge label="Detected" kind="success" />}
          </div>

          <div style={{
            position: 'relative',
            borderRadius: 'var(--radius-md)',
            overflow: 'hidden',
            marginTop: 8,
            border: '1px solid var(--color-border)',
          }}>
            <canvas
              ref={canvasRef}
              width={560}
              height={420}
              style={{ width: '100%', display: 'block', backgroundColor: '#0F172A' }}
            />
          </div>

          <div style={{ marginTop: 16, display: 'flex', gap: 8 }}>
            {status === 'idle' && (
              <Button onClick={startScanning}>Start Scanning</Button>
            )}
            {status === 'scanning' && (
              <Button onClick={startScanning}>Scanning... (Simulated)</Button>
            )}
            {status === 'detected' && (
              <>
                <Button onClick={openDetail}>Open Verification Detail</Button>
                <Button variant="outline" onClick={reset}>Scan Another</Button>
              </>
            )}
          </div>
        </Card>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          <Card>
            <div className="section-header">
              <h3>Manual Input</h3>
              <Badge label="Fallback" kind="neutral" />
            </div>
            <p style={{
              fontSize: 13,
              color: 'var(--color-text-secondary)',
              marginTop: 4,
              marginBottom: 12,
            }}>
              Can&apos;t scan? Enter the proof ID manually.
            </p>
            <label className="label">Proof ID</label>
            <input
              className="input"
              type="text"
              value={manualInput}
              onChange={(e) => setManualInput(e.target.value)}
              placeholder="VRF-2048"
              style={{ fontFamily: 'var(--font-mono)' }}
            />
            {errorMsg && (
              <div style={{
                marginTop: 8,
                fontSize: 12,
                color: 'var(--color-error)',
              }}>
                {errorMsg}
              </div>
            )}
            <div style={{ marginTop: 12 }}>
              <Button variant="outline" onClick={handleManualSubmit}>
                Lookup Proof
              </Button>
            </div>
          </Card>

          <Card>
            <div className="section-header">
              <h3>Scan Tips</h3>
            </div>
            <ul style={{
              margin: 0,
              paddingLeft: 18,
              fontSize: 13,
              color: 'var(--color-text-secondary)',
              lineHeight: 1.8,
            }}>
              <li>Hold the device screen 20–30 cm from the camera.</li>
              <li>Ensure the QR code is fully visible in the frame.</li>
              <li>QR codes expire after 10 minutes for security.</li>
              <li>Each QR is single-use per verification session.</li>
            </ul>
          </Card>

          {scannedId && (
            <Card>
              <div className="section-header">
                <h3>Last Detected</h3>
                <Badge label="Ready" kind="success" />
              </div>
              <div style={{
                fontFamily: 'var(--font-mono)',
                fontSize: 18,
                fontWeight: 700,
                color: 'var(--color-primary-dark)',
                padding: '12px 0',
              }}>
                #{scannedId}
              </div>
              <div style={{ fontSize: 12, color: 'var(--color-text-secondary)' }}>
                Detected at {new Date().toLocaleTimeString()}
              </div>
            </Card>
          )}
        </div>
      </div>
    </>
  );
}
