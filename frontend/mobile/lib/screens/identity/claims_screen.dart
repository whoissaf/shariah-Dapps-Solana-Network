import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/app_card.dart';
import '../../design/components/status_badge.dart';
import '../../design/components/empty_state.dart';
import '../../navigation/app_router.dart';

class _ClaimType {
  final String id;
  final String title;
  final String description;
  final IconData icon;
  final Color iconColor;
  final StatusKind kind;
  final String status;
  final String? value;

  const _ClaimType({
    required this.id,
    required this.title,
    required this.description,
    required this.icon,
    required this.iconColor,
    required this.kind,
    required this.status,
    this.value,
  });
}

class ClaimsScreen extends StatelessWidget {
  const ClaimsScreen({super.key});

  static const List<_ClaimType> _claims = [
    _ClaimType(
      id: 'income',
      title: 'Income Threshold',
      description: 'Prove your monthly income meets the minimum requirement.',
      icon: Icons.attach_money,
      iconColor: AppColors.success,
      kind: StatusKind.success,
      status: 'Verified',
      value: 'IDR 7,000,000',
    ),
    _ClaimType(
      id: 'age',
      title: 'Age Minimum',
      description: 'Prove you meet the minimum age requirement (≥ 21).',
      icon: Icons.cake_outlined,
      iconColor: AppColors.info,
      kind: StatusKind.info,
      status: 'Pending',
      value: 'Age: 25',
    ),
    _ClaimType(
      id: 'business',
      title: 'Business Category',
      description: 'Prove your business operates in halal category.',
      icon: Icons.storefront_outlined,
      iconColor: AppColors.warning,
      kind: StatusKind.neutral,
      status: 'Not Started',
    ),
    _ClaimType(
      id: 'financing',
      title: 'No Restricted Financing',
      description: 'Prove you have no active restricted financing.',
      icon: Icons.credit_score_outlined,
      iconColor: AppColors.primary,
      kind: StatusKind.neutral,
      status: 'Not Started',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Claims'),
        backgroundColor: AppColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text(
          'New Claim',
          style: TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w600,
          ),
        ),
        onPressed: () {
          Navigator.of(context).pushNamed(AppRoutes.createClaim);
        },
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Your Claims',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 6),
              Text(
                'Manage your zero-knowledge claim proofs.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: AppColors.textSecondary,
                    ),
              ),
              const SizedBox(height: 20),

              AppCard(
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        '4',
                        style: TextStyle(
                          fontFamily: 'Inter',
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: AppColors.primary,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Text(
                        'Total claim types available',
                        style: TextStyle(
                          fontFamily: 'Inter',
                          fontSize: 13,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              ..._claims.map((claim) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: _ClaimCard(claim: claim),
                  )),

              const SizedBox(height: 16),

              const EmptyState(
                icon: Icons.info_outline,
                title: 'Need more claim types?',
                subtitle:
                    'Additional claim categories will be available in the next release.',
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ClaimCard extends StatelessWidget {
  final _ClaimType claim;

  const _ClaimCard({required this.claim});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.surface,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          Navigator.of(context).pushNamed(AppRoutes.createClaim);
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
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: claim.iconColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(claim.icon, color: claim.iconColor, size: 22),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          claim.title,
                          style: const TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        if (claim.value != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            claim.value!,
                            style: const TextStyle(
                              fontFamily: 'JetBrains Mono',
                              fontSize: 12,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  StatusBadge(label: claim.status, kind: claim.kind),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                claim.description,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      height: 1.5,
                      color: AppColors.textSecondary,
                    ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
