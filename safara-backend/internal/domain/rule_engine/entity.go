package rule_engine

import (
	"time"

	"github.com/google/uuid"
)

type RuleSnapshot struct {
	ID              uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	VerifiedEventID uuid.UUID `gorm:"type:uuid;uniqueIndex;not null" json:"verified_event_id"`
	AttentionLevel  string    `gorm:"type:varchar(20);not null" json:"attention_level"`
	Confidence      int       `gorm:"type:int;not null" json:"confidence"`
	Recommendation  string    `gorm:"type:varchar(255);not null" json:"recommendation"`
	GeneratedAt     time.Time `json:"generated_at"`
}

func (RuleSnapshot) TableName() string {
	return "rule_snapshots"
}

type RuleReason struct {
	ID          uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	SnapshotID  uuid.UUID `gorm:"type:uuid;not null" json:"snapshot_id"`
	ReasonCode  string    `gorm:"type:varchar(20);not null" json:"reason_code"`
	Description string    `gorm:"type:varchar(255);not null" json:"description"`
	Weight      int       `gorm:"type:int;not null" json:"weight"`
}

func (RuleReason) TableName() string {
	return "rule_reasons"
}
