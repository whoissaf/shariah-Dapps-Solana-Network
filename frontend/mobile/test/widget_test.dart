import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:identity_wallet/main.dart';

void main() {
  testWidgets('splash renders title and preview card', (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());

    expect(find.text('Identity Wallet'), findsOneWidget);
    expect(find.text('Preview'), findsOneWidget);
    expect(find.text('ACTIVE'), findsOneWidget);
    expect(find.text('Design System Ready'), findsOneWidget);
    expect(find.byType(ElevatedButton), findsOneWidget);
  });
}
