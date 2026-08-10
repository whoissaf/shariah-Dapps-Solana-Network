import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/mono_hash.dart';

class QrShareScreen extends StatelessWidget {
  final String proofId;
  final String qrContent;
  final String expiresAt;

  const QrShareScreen({
    super.key,
    required this.proofId,
    required this.qrContent,
    required this.expiresAt,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Share Proof'),
        backgroundColor: AppColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.close, color: AppColors.textPrimary),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              const SizedBox(height: 12),
              Text(
                'Scan to Verify',
                style: Theme.of(context).textTheme.headlineMedium,
              ),
              const SizedBox(height: 8),
              Text(
                'Show this QR code to the verifier to verify your proof.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: AppColors.textSecondary,
                    ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 32),

              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: _QrPattern(
                        data: qrContent,
                      ),
                    ),
                    const SizedBox(height: 20),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.error.withOpacity(0.10),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(
                            Icons.timer_outlined,
                            color: AppColors.error,
                            size: 16,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'Expires in $expiresAt',
                            style: const TextStyle(
                              fontFamily: 'Inter',
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: AppColors.error,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Proof ID',
                      style: TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 11,
                        color: AppColors.textSecondary,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Text(
                          '#$proofId',
                          style: const TextStyle(
                            fontFamily: 'JetBrains Mono',
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                      ],
                    ),
                    const Divider(height: 20),
                    const Text(
                      'Content',
                      style: TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 11,
                        color: AppColors.textSecondary,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 4),
                    MonoHash(value: qrContent, maxChars: 30),
                  ],
                ),
              ),

              const Spacer(),

              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.refresh, size: 18),
                  label: const Text('Regenerate QR'),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('QR regenerated'),
                        backgroundColor: AppColors.success,
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: 12),

              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('Done'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _QrPattern extends StatelessWidget {
  final String data;

  const _QrPattern({required this.data});

  @override
  Widget build(BuildContext context) {
    final hash = data.hashCode;
    const size = 21;
    final grid = List.generate(size, (row) {
      return List.generate(size, (col) {
        if (row < 7 && col < 7) return _isFinderCell(row, col, 0, 0);
        if (row < 7 && col >= size - 7) {
          return _isFinderCell(row, col, 0, size - 7);
        }
        if (row >= size - 7 && col < 7) {
          return _isFinderCell(row, col, size - 7, 0);
        }
        return (((row * 31 + col * 17 + hash) % 7) < 3);
      });
    });

    return Container(
      width: 240,
      height: 240,
      padding: const EdgeInsets.all(8),
      color: Colors.white,
      child: GridView.count(
        crossAxisCount: size,
        physics: const NeverScrollableScrollPhysics(),
        children: [
          for (var row = 0; row < size; row++)
            for (var col = 0; col < size; col++)
              Container(
                decoration: BoxDecoration(
                  color: grid[row][col] ? AppColors.secondary : Colors.white,
                  borderRadius: BorderRadius.circular(1),
                ),
                margin: const EdgeInsets.all(0.2),
              ),
        ],
      ),
    );
  }

  bool _isFinderCell(int row, int col, int originRow, int originCol) {
    final localRow = row - originRow;
    final localCol = col - originCol;
    if (localRow == 0 ||
        localRow == 6 ||
        localCol == 0 ||
        localCol == 6) {
      return true;
    }
    if (localRow >= 2 && localRow <= 4 && localCol >= 2 && localCol <= 4) {
      return true;
    }
    return false;
  }
}
