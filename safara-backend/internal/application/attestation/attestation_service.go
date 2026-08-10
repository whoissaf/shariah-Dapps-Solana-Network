package attestation

import (
	"context"
	"crypto/sha256"
	"encoding/hex"
	"fmt"
	"safara-backend/internal/domain/attestation"
	"safara-backend/internal/domain/verified_event"

	"github.com/google/uuid"
)

type Service interface {
	AttestEvent(ctx context.Context, verifiedEventID uuid.UUID) (*attestation.Attestation, error)
}

type service struct {
	repo              attestation.Repository
	verifiedEventRepo verified_event.Repository
}

func NewService(repo attestation.Repository, verifiedEventRepo verified_event.Repository) Service {
	return &service{
		repo:              repo,
		verifiedEventRepo: verifiedEventRepo,
	}
}

func (s *service) AttestEvent(ctx context.Context, verifiedEventID uuid.UUID) (*attestation.Attestation, error) {
	event, err := s.verifiedEventRepo.GetByID(ctx, verifiedEventID)
	if err != nil {
		return nil, err
	}

	payload := fmt.Sprintf("%s:%s:%d", event.ReportID.String(), event.VerificationID.String(), event.Version)
	hash := sha256.Sum256([]byte(payload))
	hashHex := hex.EncodeToString(hash[:])

	mockTxHash := "0x" + hashHex[:64]
	mockAttestationID := "attest_" + hashHex[:16]

	entity := &attestation.Attestation{
		VerifiedEventID: verifiedEventID,
		Chain:           "Creditcoin Testnet",
		Network:         "Testnet",
		AttestationID:   mockAttestationID,
		TxHash:          mockTxHash,
		Status:          "verified",
	}

	if err := s.repo.Create(ctx, entity); err != nil {
		return nil, err
	}

	return entity, nil
}
