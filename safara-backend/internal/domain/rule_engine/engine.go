package rule_engine

import (
	"safara-backend/internal/domain/report"
)

type CalculationResult struct {
	AttentionLevel string
	Confidence     int
	Recommendation string
	Reasons        []ReasonData
}

type ReasonData struct {
	ReasonCode  string
	Description string
	Weight      int
}

type Engine struct{}

func NewEngine() *Engine {
	return &Engine{}
}

func (e *Engine) Calculate(r *report.Report) CalculationResult {
	weight := 30
	reasons := []ReasonData{
		{
			ReasonCode:  "RF011",
			Description: "Single Community Report",
			Weight:      30,
		},
	}

	attention := "Green"
	if weight >= 80 {
		attention = "Red"
	} else if weight >= 60 {
		attention = "Orange"
	} else if weight >= 30 {
		attention = "Yellow"
	}

	confidence := 40
	recommendation := "Stay Alert"
	if attention == "Green" {
		recommendation = "Proceed Normally"
	} else if attention == "Orange" {
		recommendation = "Review Latest Evidence Before Travel"
	} else if attention == "Red" {
		recommendation = "Delay Travel If Possible"
	}

	return CalculationResult{
		AttentionLevel: attention,
		Confidence:     confidence,
		Recommendation: recommendation,
		Reasons:        reasons,
	}
}
