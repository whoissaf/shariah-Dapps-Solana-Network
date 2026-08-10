<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinalBackendAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_backend_user_and_verifier_journey(): void
    {
        Storage::fake('local');
        Mail::fake();

        $register = $this->postJson('/api/auth/register', [
            'name' => 'Final User',
            'email' => 'final.user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $register->assertCreated();

        $otpCode = null;

        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$otpCode) {
            $otpCode = $mail->code;

            return true;
        });

        $this->postJson('/api/auth/verify-email', [
            'email' => 'final.user@example.com',
            'code' => $otpCode,
        ])->assertOk();

        $login = $this->postJson('/api/auth/login', [
            'email' => 'final.user@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk();

        $userToken = $login->json('token');

        $userHeaders = [
            'Authorization' => 'Bearer ' . $userToken,
            'Accept' => 'application/json',
        ];

        $this->withHeaders($userHeaders)
            ->postJson('/api/wallet/connect', [
                'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
            ])
            ->assertOk();

        $this->withHeaders($userHeaders)
            ->postJson('/api/identity/create')
            ->assertCreated();

        $claimResponse = $this->withHeaders($userHeaders)
            ->postJson('/api/claims/create', [
                'claim_type' => 'income_threshold',
                'payload' => [
                    'monthly_income' => 7000000,
                ],
            ]);

        $claimResponse->assertCreated();

        $claimId = $claimResponse->json('claim.id');

        $file = UploadedFile::fake()->create('salary.pdf', 100, 'application/pdf');

        $uploadResponse = $this->withHeaders($userHeaders)
            ->post('/api/documents/upload', [
                'claim_id' => $claimId,
                'document_type' => 'salary',
                'file' => $file,
            ]);

        $uploadResponse->assertCreated();

        $ruleResponse = $this->withHeaders($userHeaders)
            ->postJson('/api/rules/validate', [
                'claim_id' => $claimId,
            ]);

        $ruleResponse->assertOk();
        $ruleResponse->assertJsonPath('eligible', true);

        $proofResponse = $this->withHeaders($userHeaders)
            ->postJson('/api/proof/generate', [
                'claim_id' => $claimId,
            ]);

        $proofResponse->assertCreated();

        $proofId = $proofResponse->json('proof.id');

        $this->withHeaders($userHeaders)
            ->postJson('/api/blockchain/store', [
                'proof_id' => $proofId,
            ])
            ->assertCreated();

        $shareResponse = $this->withHeaders($userHeaders)
            ->postJson('/api/proof/share', [
                'proof_id' => $proofId,
            ]);

        $shareResponse->assertOk();

        $qrNonce = $shareResponse->json('qr.qr_nonce');
        $qrSignature = $shareResponse->json('qr.qr_signature');
        $qrExpiresAt = $shareResponse->json('qr.qr_expires_at');

        $historyResponse = $this->withHeaders($userHeaders)
            ->getJson('/api/history');

        $historyResponse->assertOk();
        $historyResponse->assertJsonCount(1, 'history');

        $profileResponse = $this->withHeaders($userHeaders)
            ->getJson('/api/profile');

        $profileResponse->assertOk();
        $profileResponse->assertJsonPath('user.email', 'final.user@example.com');

        $verifier = User::create([
            'name' => 'Final Verifier',
            'email' => 'final.verifier@example.com',
            'password' => 'password123',
            'role' => 'verifier',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        $verifierLogin = $this->postJson('/api/auth/login', [
            'email' => $verifier->email,
            'password' => 'password123',
        ]);

        $verifierLogin->assertOk();
        $verifierLogin->assertJsonPath('user.role', 'verifier');

        $verifierToken = $verifierLogin->json('token');

        $verifierHeaders = [
            'Authorization' => 'Bearer ' . $verifierToken,
            'Accept' => 'application/json',
        ];

        $this->app['auth']->forgetGuards();

        $dashboardBefore = $this->withHeaders($verifierHeaders)
            ->getJson('/api/dashboard');

        $dashboardBefore->assertOk();
        $dashboardBefore->assertJsonPath('dashboard.pending', 0);
        $dashboardBefore->assertJsonPath('dashboard.verified', 0);
        $dashboardBefore->assertJsonPath('dashboard.rejected', 0);

        $readResponse = $this->withHeaders($verifierHeaders)
            ->postJson('/api/verification/read', [
                'proof_id' => $proofId,
                'nonce' => $qrNonce,
                'signature' => $qrSignature,
                'expires_at' => $qrExpiresAt,
            ]);

        $readResponse->assertOk();
        $readResponse->assertJsonPath('proof.proof_id', $proofId);

        $fetchProofResponse = $this->withHeaders($verifierHeaders)
            ->getJson('/api/proof/' . $proofId);

        $fetchProofResponse->assertOk();
        $fetchProofResponse->assertJsonPath('proof.proof_id', $proofId);

        $verifyResponse = $this->withHeaders($verifierHeaders)
            ->postJson('/api/verification/verify', [
                'proof_id' => $proofId,
            ]);

        $verifyResponse->assertOk();
        $verifyResponse->assertJsonPath('verification.technical_passed', true);

        $verificationId = $verifyResponse->json('verification.id');

        $explainResponse = $this->withHeaders($verifierHeaders)
            ->postJson('/api/ai/explain', [
                'verification_id' => $verificationId,
            ]);

        $explainResponse->assertOk();
        $explainResponse->assertJsonPath('explanation.recommendation', 'approve');

        $approveResponse = $this->withHeaders($verifierHeaders)
            ->postJson('/api/verification/approve', [
                'verification_id' => $verificationId,
            ]);

        $approveResponse->assertOk();
        $approveResponse->assertJsonPath('verification.status', 'verified');
        $approveResponse->assertJsonPath('proof.status', 'verified');

        $auditResponse = $this->withHeaders($verifierHeaders)
            ->getJson('/api/audit');

        $auditResponse->assertOk();
        $auditResponse->assertJsonCount(1, 'audit');
        $auditResponse->assertJsonPath('audit.0.verification_id', $verificationId);

        $reportResponse = $this->withHeaders($verifierHeaders)
            ->get('/api/report/export?status=verified');

        $reportResponse->assertOk();
        $reportResponse->assertDownload('verification-report.csv');

        $dashboardAfter = $this->withHeaders($verifierHeaders)
            ->getJson('/api/dashboard');

        $dashboardAfter->assertOk();
        $dashboardAfter->assertJsonPath('dashboard.verified', 1);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        $historyAfter = $this->withHeaders($userHeaders)
            ->getJson('/api/history');

        $historyAfter->assertOk();
        $historyAfter->assertJsonCount(1, 'history');
        $historyAfter->assertJsonPath('history.0.display_status', 'verified');

        $profileAfter = $this->withHeaders($userHeaders)
            ->getJson('/api/profile');

        $profileAfter->assertOk();
        $profileAfter->assertJsonPath('summary.proofs_verified', 1);
    }
}
