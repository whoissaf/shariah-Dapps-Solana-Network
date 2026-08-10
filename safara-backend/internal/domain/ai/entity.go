package ai

import (
	"time"

	"github.com/google/uuid"
)

type AIExplanation struct {
	ID          uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	SnapshotID  uuid.UUID `gorm:"type:uuid;uniqueIndex;not null" json:"snapshot_id"`
	Summary     string    `gorm:"type:text;not null" json:"summary"`
	Explanation string    `gorm:"type:text;not null" json:"explanation"`
	Language    string    `gorm:"type:varchar(10);default:'id'" json:"language"`
	Model       string    `gorm:"type:varchar(100)" json:"model"`
	CreatedAt   time.Time `json:"created_at"`
}

func (AIExplanation) TableName() string {
	return "ai_explanations"
}
