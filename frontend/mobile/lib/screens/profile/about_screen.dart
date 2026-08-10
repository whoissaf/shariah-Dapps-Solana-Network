import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/app_card.dart';
import '../../design/components/mono_hash.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('About'),
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
              Center(
                child: Column(
                  children: [
                    Container(
                      width: 72,
                      height: 72,
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Icon(
                        Icons.shield_outlined,
                        color: Colors.white,
                        size: 36,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'Identity Wallet',
                      style: Theme.of(context).textTheme.headlineMedium,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Private identity for Sharia-compliant finance.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppColors.textSecondary,
                          ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 32),

              Text(
                'Application',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              AppCard(
                child: Column(
                  children: const [
                    _AboutRow(label: 'Version', value: '1.0.0-mvp'),
                    SizedBox(height: 16),
                    Divider(height: 1),
                    SizedBox(height: 16),
                    _AboutRow(label: 'Build', value: '2026.08.10', mono: true),
                    SizedBox(height: 16),
                    Divider(height: 1),
                    SizedBox(height: 16),
                    _AboutRow(label: 'Platform', value: 'Flutter + Laravel'),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              Text(
                'Blockchain Network',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const _AboutRow(
                      label: 'Network',
                      value: 'Ethereum (Sepolia Testnet)',
                    ),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 16),
                    const Text(
                      'Contract Address',
                      style: TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 11,
                        color: AppColors.textSecondary,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const MonoHash(
                      value: '0x5fbdb2315678afecb367f032d93f642f64180aa3',
                      maxChars: 20,
                    ),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 16),
                    const _AboutRow(
                      label: 'Privacy Technology',
                      value: 'Semaphore + Groth16',
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              Text(
                'Privacy Architecture',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              AppCard(
                child: Column(
                  children: const [
                    _FeatureItem(
                      icon: Icons.fingerprint,
                      title: 'Anonymous Identity',
                      subtitle: 'Identity commitment stored on-chain, secret stays on device.',
                    ),
                    SizedBox(height: 16),
                    Divider(height: 1),
                    SizedBox(height: 16),
                    _FeatureItem(
                      icon: Icons.verified_user_outlined,
                      title: 'Zero-Knowledge Proofs',
                      subtitle: 'Prove eligibility without revealing sensitive data.',
                    ),
                    SizedBox(height: 16),
                    Divider(height: 1),
                    SizedBox(height: 16),
                    _FeatureItem(
                      icon: Icons.mosque_outlined,
                      title: 'Sharia Compliance',
                      subtitle: 'Halal business categories and no riba financing.',
                    ),
                    SizedBox(height: 16),
                    Divider(height: 1),
                    SizedBox(height: 16),
                    _FeatureItem(
                      icon: Icons.shield_outlined,
                      title: 'On-Chain Audit',
                      subtitle: 'All verifications anchored to Ethereum with full traceability.',
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              Text(
                'License',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              AppCard(
                child: Column(
                  children: [
                    const _AboutRow(label: 'License', value: 'MIT License'),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 16),
                    Text(
                      'Identity Wallet is open-source software released under the MIT License. You are free to use, modify, and distribute this software in compliance with the license terms.',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            height: 1.6,
                            color: AppColors.textSecondary,
                          ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              Text(
                'Team',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 12),

              AppCard(
                child: Column(
                  children: const [
                    _TeamMember(name: 'Backend Engineer', role: 'Laravel + Solidity'),
                    SizedBox(height: 12),
                    Divider(height: 1),
                    SizedBox(height: 12),
                    _TeamMember(name: 'Flutter Developer', role: 'Mobile Identity Wallet'),
                    SizedBox(height: 12),
                    Divider(height: 1),
                    SizedBox(height: 12),
                    _TeamMember(name: 'Next.js Developer', role: 'Verifier Portal'),
                    SizedBox(height: 12),
                    Divider(height: 1),
                    SizedBox(height: 12),
                    _TeamMember(name: 'AI Engineer', role: 'LLM Explanation Service'),
                  ],
                ),
              ),

              const SizedBox(height: 40),

              Center(
                child: Text(
                  'Made with ❤️ for Ethereum Privacy Hackathon 2026',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppColors.textMuted,
                      ),
                  textAlign: TextAlign.center,
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

class _AboutRow extends StatelessWidget {
  final String label;
  final String value;
  final bool mono;

  const _AboutRow({
    required this.label,
    required this.value,
    this.mono = false,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          flex: 2,
          child: Text(
            label,
            style: const TextStyle(
              fontFamily: 'Inter',
              fontSize: 12,
              color: AppColors.textSecondary,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        Expanded(
          flex: 3,
          child: Text(
            value,
            style: TextStyle(
              fontFamily: mono ? 'JetBrains Mono' : 'Inter',
              fontSize: 13,
              color: AppColors.textPrimary,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
      ],
    );
  }
}

class _FeatureItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;

  const _FeatureItem({
    required this.icon,
    required this.title,
    required this.subtitle,
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
            color: AppColors.primary.withOpacity(0.12),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 18, color: AppColors.primary),
        ),
        const SizedBox(width: 12),
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
              const SizedBox(height: 4),
              Text(
                subtitle,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      height: 1.5,
                      color: AppColors.textSecondary,
                    ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _TeamMember extends StatelessWidget {
  final String name;
  final String role;

  const _TeamMember({required this.name, required this.role});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: AppColors.surfaceMuted,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: AppColors.border),
          ),
          child: const Icon(
            Icons.person_outline,
            size: 18,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                name,
                style: const TextStyle(
                  fontFamily: 'Inter',
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
              ),
              Text(
                role,
                style: const TextStyle(
                  fontFamily: 'Inter',
                  fontSize: 11,
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
