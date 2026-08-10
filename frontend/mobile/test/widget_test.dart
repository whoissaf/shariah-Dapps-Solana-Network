import 'package:flutter_test/flutter_test.dart';
import 'package:identity_wallet/main.dart';

void main() {
  testWidgets('splash renders app title', (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());

    expect(find.text('Identity Wallet'), findsOneWidget);
  });
}
