import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/app_card.dart';
import '../../design/components/status_badge.dart';

class _HistoryEvent {
  final String title;
  final String subtitle;
  final String time;
  final StatusKind kind;
  final IconData icon;

  const _HistoryEvent({
    required this.title,
    required this.subtitle,
    required this.time,
    required this.kind,
    required this.icon,
  });
}

class HistoryScreen extends StatelessWidget {
  const HistoryScreen({super.key});

  static const List<_HistoryEvent> _events = [
    _HistoryEvent(
      title: 'Proof Verified',
      subtitle: 'Income Threshold claim has been verified by compliance officer.',
      time: '2 minutes ago',
      kind: StatusKind.success,
      icon: Icons.check_circle_outline,
    ),
    _HistoryEvent(
      title: 'QR Scanned',
      subtitle: 'Verifier scanned your QR code to start verification.',
      time: '3 minutes ago',
      kind: StatusKind.info,
      icon: Icons.qr_code_scanner,
    ),
    _HistoryEvent(
      title: 'QR Shared',
      subtitle: 'You generated a QR code for proof sharing.',
      time: '5 minutes ago',
      kind: StatusKind.info,
      icon: Icons.share,
    ),
    _HistoryEvent(
      title: 'Proof Generated',
      subtitle: 'Zero-knowledge proof generated and stored on-chain.',
      time: '10 minutes ago',
      kind: StatusKind.success,
      icon: Icons.verified_user_outlined,
    ),
    _HistoryEvent(
      title: 'Blockchain Transaction',
      subtitle: 'Proof hash stored on Ethereum (TX: 0x943a...8c21d4).',
      time: '10 minutes ago',
      kind: StatusKind.success,
      icon: Icons.link,
    ),
    _HistoryEvent(
      title: 'Rule Validation',
      subtitle: 'All eligibility rules passed successfully.',
      time: '11 minutes ago',
      kind: StatusKind.success,
      icon: Icons.rule,
    ),
    _HistoryEvent(
      title: 'Document Uploaded',
      subtitle: 'Salary slip uploaded as supporting document.',
      time: '15 minutes ago',
      kind: StatusKind.neutral,
      icon: Icons.upload_file_outlined,
    ),
    _HistoryEvent(
      title: 'Claim Created',
      subtitle: 'Income Threshold claim was submitted for processing.',
      time: '20 minutes ago',
      kind: StatusKind.info,
      icon: Icons.add_circle_outline,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Verification History'),
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
                'Activity Timeline',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 6),
              Text(
                'Complete record of your identity and verification activities.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: AppColors.textSecondary,
                    ),
              ),
              const SizedBox(height: 24),

              ..._events.asMap().entries.map((entry) {
                final index = entry.key;
                final event = entry.value;
                final isLast = index == _events.length - 1;
                return _TimelineTile(
                  event: event,
                  isLast: isLast,
                );
              }),

              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }
}

class _TimelineTile extends StatelessWidget {
  final _HistoryEvent event;
  final bool isLast;

  const _TimelineTile({required this.event, required this.isLast});

  Color _iconColor() {
    switch (event.kind) {
      case StatusKind.success:
        return AppColors.success;
      case StatusKind.error:
        return AppColors.error;
      case StatusKind.warning:
        return AppColors.warning;
      case StatusKind.info:
        return AppColors.info;
      default:
        return AppColors.textSecondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: _iconColor().withOpacity(0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(event.icon, color: _iconColor(), size: 20),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 40,
                color: AppColors.border,
              ),
          ],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          event.title,
                          style: const TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                      ),
                      StatusBadge(
                        label: _statusLabel(),
                        kind: event.kind,
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    event.subtitle,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          height: 1.5,
                          color: AppColors.textSecondary,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(
                        Icons.access_time,
                        size: 12,
                        color: AppColors.textMuted,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        event.time,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontSize: 11,
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  String _statusLabel() {
    switch (event.kind) {
      case StatusKind.success:
        return 'Completed';
      case StatusKind.error:
        return 'Failed';
      case StatusKind.warning:
        return 'Warning';
      case StatusKind.info:
        return 'Info';
      default:
        return 'Log';
    }
  }
}
