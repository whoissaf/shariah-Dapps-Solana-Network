import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../design/theme.dart';
import '../../design/components/app_card.dart';
import '../../design/components/status_badge.dart';
import '../../design/components/mono_hash.dart';
import '../../navigation/app_router.dart';

class ProofDetailScreen extends StatelessWidget {
  final Map<String, dynamic> proof;

  const ProofDetailScreen({super.key, required this.proof});

  StatusKind _statusKind(String status) {
    switch (status.toLowerCase()) {
      case 'verified':
        return StatusKind.success;
      case 'pending':
        return StatusKind.info;
      case 'rejected':
        return StatusKind.error;
      case 'generated':
        return StatusKind.warning;
      default:
        return StatusKind.neutral;
    }
  }

  @override
  Widget build(BuildContext context) {
    final id = proof['id']?.toString() ?? '0';
    final claimType = proof['claimType']?.toString() ?? 'Claim';
    final status = proof['status']?.toString() ?? 'Generated';
    final proofHash = proof['proofHash']?.toString() ?? '';
    final createdAt = proof['createdAt']?.toString() ?? '';
    final blockchainTx = proof['blockchainTx']?.toString();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Proof Detail'),
        backgroundColor: AppColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(18),
                      ),
                      child: const Icon(
                        Icons.verified_user,
                        color: AppColors.primary,
                        size: 32,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      claimType,
                      style: Theme.of(context).textTheme.titleLarge,
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Proof #$id',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppColors.textSecondary,
                          ),
                    ),
                    const SizedBox(height: 12),
                    StatusBadge(
                      label: status.toUpperCase(),
                      kind: _statusKind(status),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              Text(
                'Proof Information',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              AppCard(
                child: Column(
                  children: [
                    _DetailRow(
                      icon: Icons.label_outline,
                      label: 'Claim Type',
                      value: claimType,
                    ),
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 12),
                      child: Divider(height: 1),
                    ),
                    _DetailRow(
                      icon: Icons.access_time,
                      label: 'Created',
                      value: createdAt,
                    ),
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 12),
                      child: Divider(height: 1),
                    ),
                    _DetailRow(
                      icon: Icons.fingerprint,
                      label: 'Proof Hash',
                      valueWidget: MonoHash(value: proofHash, maxChars: 20),
                      onCopy: () {
                        Clipboard.setData(ClipboardData(text: proofHash));
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Hash copied'),
                            backgroundColor: AppColors.success,
                          ),
                        );
                      },
                    ),
                    if (blockchainTx != null) ...[
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 12),
                        child: Divider(height: 1),
                      ),
                      _DetailRow(
                        icon: Icons.link,
                        label: 'Blockchain TX',
                        valueWidget: MonoHash(value: blockchainTx, maxChars: 16),
                        onCopy: () {
                          Clipboard.setData(ClipboardData(text: blockchainTx));
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('TX hash copied'),
                              backgroundColor: AppColors.success,
                            ),
                          );
                        },
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(height: 20),

              Text(
                'Verification',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              AppCard(
                child: Column(
                  children: const [
                    _VerificationStep(
                      icon: Icons.check_circle_outline,
                      label: 'Proof Valid',
                      value: 'Verified',
                      kind: StatusKind.success,
                    ),
                    SizedBox(height: 12),
                    _VerificationStep(
                      icon: Icons.fingerprint,
                      label: 'Identity Commitment',
                      value: 'Matched',
                      kind: StatusKind.success,
                    ),
                    SizedBox(height: 12),
                    _VerificationStep(
                      icon: Icons.rule,
                      label: 'Rule Check',
                      value: 'Passed',
                      kind: StatusKind.success,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 32),

              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.qr_code_2, size: 18),
                  label: const Text('Generate QR for Verifier'),
                  onPressed: () {
                    Navigator.of(context).pushNamed(
                      AppRoutes.qrShare,
                      arguments: {
                        'proofId': id,
                        'qrContent':
                            '{"proof_id":$id,"nonce":"abc123","signature":"xyz789","expires_at":"${DateTime.now().add(const Duration(minutes: 10)).toIso8601String()}"}',
                        'expiresAt': '10:00',
                      },
                    );
                  },
                ),
              ),

              const SizedBox(height: 12),

              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  icon: const Icon(Icons.download_outlined, size: 18),
                  label: const Text('Download Proof'),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Proof downloaded')),
                    );
                  },
                ),
              ),

              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String? value;
  final Widget? valueWidget;
  final VoidCallback? onCopy;

  const _DetailRow({
    required this.icon,
    required this.label,
    this.value,
    this.valueWidget,
    this.onCopy,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            color: AppColors.surfaceMuted,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 16, color: AppColors.primary),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontFamily: 'Inter',
                  fontSize: 11,
                  fontWeight: FontWeight.w500,
                  color: AppColors.textSecondary,
                  letterSpacing: 0.3,
                ),
              ),
              const SizedBox(height: 4),
              valueWidget ??
                  Text(
                    value ?? '',
                    style: const TextStyle(
                      fontFamily: 'Inter',
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textPrimary,
                    ),
                  ),
            ],
          ),
        ),
        if (onCopy != null)
          IconButton(
            icon: const Icon(Icons.copy, size: 18, color: AppColors.primary),
            onPressed: onCopy,
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(),
          ),
      ],
    );
  }
}

class _VerificationStep extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final StatusKind kind;

  const _VerificationStep({
    required this.icon,
    required this.label,
    required this.value,
    required this.kind,
  });

  @override
  Widget build(BuildContext context) {
    Color iconColor;
    switch (kind) {
      case StatusKind.success:
        iconColor = AppColors.success;
        break;
      case StatusKind.error:
        iconColor = AppColors.error;
        break;
      case StatusKind.warning:
        iconColor = AppColors.warning;
        break;
      case StatusKind.info:
        iconColor = AppColors.info;
        break;
      default:
        iconColor = AppColors.textSecondary;
    }

    return Row(
      children: [
        Icon(icon, size: 20, color: iconColor),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(
              fontFamily: 'Inter',
              fontSize: 13,
              color: AppColors.textPrimary,
            ),
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontFamily: 'Inter',
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: iconColor,
          ),
        ),
      ],
    );
  }
}
