package ai

import (
	"net/http"
	appAI "safara-backend/internal/application/ai"
	"safara-backend/pkg/response"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

type Handler struct {
	service appAI.Service
}

func NewHandler(service appAI.Service) *Handler {
	return &Handler{service: service}
}

type GenerateExplanationRequest struct {
	SnapshotID string `json:"snapshot_id" binding:"required"`
}

func (h *Handler) GenerateExplanation(c *gin.Context) {
	var req GenerateExplanationRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload", err.Error())
		return
	}

	snapshotID, err := uuid.Parse(req.SnapshotID)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid snapshot_id format", nil)
		return
	}

	result, err := h.service.GenerateExplanation(c.Request.Context(), snapshotID)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to generate AI explanation", err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "AI explanation generated successfully", result)
}
