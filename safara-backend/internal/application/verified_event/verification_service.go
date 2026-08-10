package verified_event

import (
	"context"
	"safara-backend/internal/domain/report"
	"safara-backend/internal/domain/rule_engine"
	"safara-backend/internal/domain/verified_event"

	"github.com/google/uuid"
)

type Service interface {
	ApproveReport(ctx context.Context, reportID uuid.UUID, moderatorID uuid.UUID, note string) (*verified_event.VerifiedEvent, error)
}

type service struct {
	reportRepo        report.Repository
	verifiedEventRepo verified_event.Repository
	ruleEngine        *rule_engine.Engine
}

func NewService(reportRepo report.Repository, verifiedEventRepo verified_event.Repository, ruleEngine *rule_engine.Engine) Service {
	return &service{
		reportRepo:        reportRepo,
		verifiedEventRepo: verifiedEventRepo,
		ruleEngine:        ruleEngine,
	}
}

func (s *service) ApproveReport(ctx context.Context, reportID uuid.UUID, moderatorID uuid.UUID, note string) (*verified_event.VerifiedEvent, error) {
	rep, err := s.reportRepo.GetByID(ctx, reportID)
	if err != nil {
		return nil, err
	}

	verReq := &verified_event.VerificationRequest{
		ReportID:   reportID,
		Status:     "approved",
		AssignedTo: moderatorID,
	}
	if err := s.verifiedEventRepo.CreateVerificationRequest(ctx, verReq); err != nil {
		return nil, err
	}

	verLog := &verified_event.VerificationLog{
		VerificationID: verReq.ID,
		Action:         "APPROVE",
		Note:           note,
		CreatedBy:      moderatorID,
	}
	if err := s.verifiedEventRepo.CreateVerificationLog(ctx, verLog); err != nil {
		return nil, err
	}

	if err := s.reportRepo.UpdateStatus(ctx, reportID, "verified"); err != nil {
		return nil, err
	}

	verEvent := &verified_event.VerifiedEvent{
		ReportID:       reportID,
		VerificationID: verReq.ID,
		Version:        1,
		EventTime:      rep.SubmittedAt,
		Status:         "verified",
	}
	if err := s.verifiedEventRepo.CreateVerifiedEvent(ctx, verEvent); err != nil {
		return nil, err
	}

	calc := s.ruleEngine.Calculate(rep)

	snapshot := &rule_engine.RuleSnapshot{
		VerifiedEventID: verEvent.ID,
		AttentionLevel:  calc.AttentionLevel,
		Confidence:      calc.Confidence,
		Recommendation:  calc.Recommendation,
	}
	if err := s.verifiedEventRepo.CreateRuleSnapshot(ctx, snapshot); err != nil {
		return nil, err
	}

	var reasons []rule_engine.RuleReason
	for _, r := range calc.Reasons {
		reasons = append(reasons, rule_engine.RuleReason{
			SnapshotID:  snapshot.ID,
			ReasonCode:  r.ReasonCode,
			Description: r.Description,
			Weight:      r.Weight,
		})
	}
	if err := s.verifiedEventRepo.CreateRuleReasons(ctx, reasons); err != nil {
		return nil, err
	}

	return verEvent, nil
}
