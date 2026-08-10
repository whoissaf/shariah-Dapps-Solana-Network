package ai

import (
	"context"
	"fmt"
	"safara-backend/internal/domain/ai"
	"safara-backend/internal/domain/rule_engine"
	"safara-backend/internal/domain/verified_event"

	"github.com/google/uuid"
)

type Service interface {
	GenerateExplanation(ctx context.Context, snapshotID uuid.UUID) (*ai.AIExplanation, error)
}

type service struct {
	repo              ai.Repository
	ruleRepo          rule_engine.Repository
	verifiedEventRepo verified_event.Repository
}

func NewService(repo ai.Repository, ruleRepo rule_engine.Repository, verifiedEventRepo verified_event.Repository) Service {
	return &service{
		repo:              repo,
		ruleRepo:          ruleRepo,
		verifiedEventRepo: verifiedEventRepo,
	}
}

func (s *service) GenerateExplanation(ctx context.Context, snapshotID uuid.UUID) (*ai.AIExplanation, error) {
	snapshot, err := s.ruleRepo.GetSnapshotByID(ctx, snapshotID)
	if err != nil {
		return nil, err
	}

	verEvent, err := s.verifiedEventRepo.GetByID(ctx, snapshot.VerifiedEventID)
	if err != nil {
		return nil, err
	}

	reasons, err := s.ruleRepo.GetReasonsBySnapshotID(ctx, snapshotID)
	if err != nil {
		return nil, err
	}

	var reasonText string
	for i, r := range reasons {
		if i == 0 {
			reasonText = fmt.Sprintf("- %s (Bobot: %d)", r.Description, r.Weight)
		} else {
			reasonText += fmt.Sprintf("\n- %s (Bobot: %d)", r.Description, r.Weight)
		}
	}

	summary := fmt.Sprintf("Laporan di lokasi ini telah diverifikasi dan di-attest pada blockchain. Tingkat perhatian saat ini adalah %s dengan tingkat kepercayaan %d%%.", snapshot.AttentionLevel, snapshot.Confidence)
	
	explanation := fmt.Sprintf("Berdasarkan analisis evidence yang terverifikasi (Waktu Event: %s):\n\n%s\n\nRekomendasi Sistem: %s\n\nCatatan: Keputusan akhir perjalanan tetap berada di tangan Anda.", verEvent.EventTime.Format("02-01-2006 15:04"), reasonText, snapshot.Recommendation)

	entity := &ai.AIExplanation{
		SnapshotID:  snapshotID,
		Summary:     summary,
		Explanation: explanation,
		Language:    "id",
		Model:       "Mock-LLM-v1",
	}

	if err := s.repo.Create(ctx, entity); err != nil {
		return nil, err
	}

	return entity, nil
}
