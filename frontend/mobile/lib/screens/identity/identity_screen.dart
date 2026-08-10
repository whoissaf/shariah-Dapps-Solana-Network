import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../design/theme.dart';
import '../../design/components/app_card.dart';
import '../../design/components/status_badge.dart';
import '../../design/components/mono_hash.dart';
import '../../navigation/app_router.dart';

class IdentityScreen extends StatelessWidget {
  const IdentityScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Identity'),
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
              const _IdentityHeroCard(),

              const SizedBox(height: 20),

              AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const _InfoRow(
                      label: 'Anonymous ID',
                      icon: Icons.fingerprint,
                    ),
                    const SizedBox(height: 8),
                    const MonoHash(
                      value: 'a3b4c5d6-e7f8-9012-a3b4-c5d6e7f89012',
                      maxChars: 18,
                    ),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 16),
                    const _InfoRow(
                      label: 'Identity Commitment',
                      icon: Icons.lock_outline,
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Expanded(
                          child: MonoHash(
                            value:
                                '0x742d35cc6634c0532925a3b844bc9e7595f0beb1e9e0c5b67a1d8f2345c6b789',
                            maxChars: 16,
                          ),
                        ),
                        IconButton(
                          icon: const Icon(
                            Icons.copy,
                            size: 18,
                            color: AppColors.primary,
                          ),
                          onPressed: () {
                            Clipboard.setData(const ClipboardData(
                              text:
                                  '0x742d35cc6634c0532925a3b844bc9e7595f0beb1e9e0c5b67a1d8f2345c6b789',
                            ));
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Commitment copied'),
                                backgroundColor: AppColors.success,
                              ),
                            );
                          },
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 16),
                    const _InfoRow(
                      label: 'Linked Wallet',
                      icon: Icons.account_balance_wallet_outlined,
                    ),
                    const SizedBox(height: 8),
                    const MonoHash(
                      value: '0x742d35cc6634c0532925a3b844bc9e7595f0beb1',
                      maxChars: 16,
                    ),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 16),
                    const _InfoRow(
                      label: 'Status',
                      icon: Icons.shield_outlined,
                    ),
                    const SizedBox(height: 8),
                    const StatusBadge(label: 'Active', kind: StatusKind.success),
                    const SizedBox(height: 12),
                    Text(
                      'Your identity commitment is registered on-chain while your secret remains private on this device.',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            height: 1.5,
                          ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              Text(
                'Identity Management',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              _ActionTile(
                icon: Icons.refresh,
                title: 'Refresh Commitment',
                subtitle: 'Generate a new commitment from the same identity.',
                onTap: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Commitment refreshed')),
                  );
                },
              ),

              const SizedBox(height: 12),

              _ActionTile(
                icon: Icons.download_outlined,
                title: 'Backup Identity',
                subtitle: 'Export encrypted identity backup to secure storage.',
                onTap: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Backup feature coming soon')),
                  );
                },
              ),

              const SizedBox(height: 12),

              _ActionTile(
                icon: Icons.delete_outline,
                title: 'Delete Identity',
                subtitle: 'Revoke this identity permanently.',
                danger: true,
                onTap: () {
                  _showConfirmDialog(context);
                },
              ),

              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  void _showConfirmDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Identity'),
        content: const Text(
          'This action will revoke your identity and all associated proofs. This cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Identity revoked'),
                  backgroundColor: AppColors.error,
                ),
              );
            },
            style: TextButton.styleFrom(foregroundColor: AppColors.error),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }
}

class _IdentityHeroCard extends StatelessWidget {
  const _IdentityHeroCard();

  @override
  Widget build(BuildContext context) {
    return Container(
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
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Icon(
              Icons.fingerprint,
              color: AppColors.primary,
              size: 36,
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'Anonymous Identity',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          const SizedBox(height: 6),
          Text(
            'Your privacy-preserving identity is active and ready to generate proofs.',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: AppColors.textSecondary,
                ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          const StatusBadge(
            label: 'ACTIVE',
            kind: StatusKind.success,
          ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final String label;
  final IconData icon;

  const _InfoRow({required this.label, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppColors.textSecondary),
        const SizedBox(width: 6),
        Text(
          label,
          style: const TextStyle(
            fontFamily: 'Inter',
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: AppColors.textSecondary,
            letterSpacing: 0.3,
          ),
        ),
      ],
    );
  }
}

class _ActionTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  final bool danger;

  const _ActionTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
    this.danger = false,
  });

  @override
  Widget build(BuildContext context) {
    final iconColor = danger ? AppColors.error : AppColors.primary;
    final bgColor =
        danger ? AppColors.error.withOpacity(0.08) : AppColors.surfaceMuted;

    return Material(
      color: AppColors.surface,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            border: Border.all(color: AppColors.border),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: iconColor, size: 20),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 12,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.chevron_right,
                color: AppColors.textMuted,
                size: 20,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
