import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/app_card.dart';
import '../../design/components/status_badge.dart';
import '../../design/components/mono_hash.dart';
import '../../navigation/app_router.dart';

class _ProofItem {
  final String id;
  final String claimType;
  final String status;
  final StatusKind kind;
  final String proofHash;
  final String createdAt;
  final String? blockchainTx;

  const _ProofItem({
    required this.id,
    required this.claimType,
    required this.status,
    required this.kind,
    required this.proofHash,
    required this.createdAt,
    this.blockchainTx,
  });
}

class ProofsScreen extends StatelessWidget {
  const ProofsScreen({super.key});

  static const List<_ProofItem> _proofs = [
    _ProofItem(
      id: '1',
      claimType: 'Income Threshold',
      status: 'Verified',
      kind: StatusKind.success,
      proofHash: '0x742d35cc6634c0532925a3b844bc9e7595f0beb1e9e0c5b67a1d8f2345c6b789',
      createdAt: '2 min ago',
      blockchainTx: '0x943a12fb...8c21d4',
    ),
    _ProofItem(
      id: '2',
      claimType: 'Age Minimum',
      status: 'Pending',
      kind: StatusKind.info,
      proofHash: '0x3b4c5d6e7f8901234a5b6c7d8e9f0a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7',
      createdAt: '1 hour ago',
      blockchainTx: '0x5fbdb231...180aa3',
    ),
    _ProofItem(
      id: '3',
      claimType: 'Business Category',
      status: 'Rejected',
      kind: StatusKind.error,
      proofHash: '0x9f8e7d6c5b4a3210fedcba9876543210abcdef0123456789abcdef0123456789',
      createdAt: 'Yesterday',
    ),
    _ProofItem(
      id: '4',
      claimType: 'No Restricted Financing',
      status: 'Generated',
      kind: StatusKind.warning,
      proofHash: '0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef',
      createdAt: '2 days ago',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Proofs'),
        backgroundColor: AppColors.background,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list, color: AppColors.textPrimary),
            onPressed: () {},
          ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Zero-Knowledge Proofs',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 6),
              Text(
                'All your generated proofs and their verification status.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: AppColors.textSecondary,
                    ),
              ),
              const SizedBox(height: 16),

              AppCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                child: Row(
                  children: [
                    _StatChip(label: 'Total', value: '4', color: AppColors.primary),
                    const SizedBox(width: 8),
                    _StatChip(label: 'Verified', value: '1', color: AppColors.success),
                    const SizedBox(width: 8),
                    _StatChip(label: 'Pending', value: '1', color: AppColors.info),
                    const SizedBox(width: 8),
                    _StatChip(label: 'Rejected', value: '1', color: AppColors.error),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              ..._proofs.map((proof) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: _ProofCard(proof: proof),
                  )),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  final String label;
  final String value;
  final Color color;

  const _StatChip({
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(
                fontFamily: 'Inter',
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontFamily: 'Inter',
                fontSize: 10,
                fontWeight: FontWeight.w500,
                color: color,
                letterSpacing: 0.3,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProofCard extends StatelessWidget {
  final _ProofItem proof;

  const _ProofCard({required this.proof});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.surface,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          Navigator.of(context).pushNamed(
            AppRoutes.proofDetail,
            arguments: {
              'proof': {
                'id': proof.id,
                'claimType': proof.claimType,
                'status': proof.status,
                'proofHash': proof.proofHash,
                'createdAt': proof.createdAt,
                'blockchainTx': proof.blockchainTx,
              },
            },
          );
        },
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            border: Border.all(color: AppColors.border),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.verified_user_outlined,
                      color: AppColors.primary,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          proof.claimType,
                          style: const TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          proof.createdAt,
                          style: const TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 12,
                            color: AppColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  StatusBadge(label: proof.status, kind: proof.kind),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  const Text(
                    'Hash: ',
                    style: TextStyle(
                      fontFamily: 'Inter',
                      fontSize: 11,
                      color: AppColors.textMuted,
                    ),
                  ),
                  Expanded(
                    child: MonoHash(value: proof.proofHash, maxChars: 14),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
