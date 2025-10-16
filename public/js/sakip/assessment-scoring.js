/**
 * SAKIP Assessment Scoring
 * Government-style assessment scoring interface for SAKIP module
 * Provides real-time calculations, validation, and scoring workflows
 * 
 * @author SAKIP Development Team
 * @version 1.0.0
 * @since 2024
 */

(function(global, factory) {
    if (typeof exports === 'object' && typeof module !== 'undefined') {
        module.exports = factory();
    } else if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        global.SAKIP_ASSESSMENT_SCORING = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Assessment Scoring Configuration Constants
     */
    const SCORING_CONSTANTS = {
        // Scoring types
        SCORING_TYPES: {
            NUMERIC: 'numeric',
            DESCRIPTIVE: 'descriptive',
            BINARY: 'binary',
            MULTIPLE_CHOICE: 'multiple_choice',
            WEIGHTED: 'weighted',
            RUBRIC: 'rubric'
        },

        // Assessment statuses
        ASSESSMENT_STATUS: {
            DRAFT: 'draft',
            IN_PROGRESS: 'in_progress',
            COMPLETED: 'completed',
            SUBMITTED: 'submitted',
            APPROVED: 'approved',
            REJECTED: 'rejected'
        },

        // Calculation methods
        CALCULATION_METHODS: {
            AVERAGE: 'average',
            WEIGHTED_AVERAGE: 'weighted_average',
            SUM: 'sum',
            HIGHEST: 'highest',
            LOWEST: 'lowest',
            CUSTOM: 'custom'
        },

        // Score ranges
        SCORE_RANGES: {
            NUMERIC: { min: 0, max: 100 },
            DESCRIPTIVE: ['Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'],
            BINARY: [0, 1],
            GRADE: ['E', 'D', 'C', 'B', 'A']
        },

        // Weight limits
        WEIGHT_LIMITS: {
            MIN: 0,
            MAX: 100,
            DEFAULT: 1
        },

        // Performance categories
        PERFORMANCE_CATEGORIES: {
            EXCELLENT: { min: 90, max: 100, label: 'Sangat Baik', color: '#28a745' },
            GOOD: { min: 80, max: 89, label: 'Baik', color: '#17a2b8' },
            ADEQUATE: { min: 70, max: 79, label: 'Cukup', color: '#ffc107' },
            POOR: { min: 60, max: 69, label: 'Kurang', color: '#fd7e14' },
            VERY_POOR: { min: 0, max: 59, label: 'Sangat Kurang', color: '#dc3545' }
        },

        // Error messages (Indonesian)
        ERROR_MESSAGES: {
            INVALID_SCORE: 'Nilai tidak valid',
            OUT_OF_RANGE: 'Nilai di luar rentang yang diizinkan',
            MISSING_EVIDENCE: 'Bukti pendukung wajib diisi',
            INCOMPLETE_ASSESSMENT: 'Penilaian belum lengkap',
            INVALID_WEIGHT: 'Bobot tidak valid',
            CALCULATION_ERROR: 'Kesalahan dalam perhitungan',
            UNAUTHORIZED_ACTION: 'Aksi tidak diizinkan',
            LOCKED_ASSESSMENT: 'Penilaian telah dikunci'
        },

        // Success messages
        SUCCESS_MESSAGES: {
            SCORE_SAVED: 'Nilai berhasil disimpan',
            ASSESSMENT_COMPLETED: 'Penilaian berhasil diselesaikan',
            CALCULATION_UPDATED: 'Perhitungan berhasil diperbarui'
        }
    };

    /**
     * Score Calculator
     */
    class ScoreCalculator {
        constructor() {
            this.calculationMethods = new Map();
            this.setupCalculationMethods();
        }

        /**
         * Setup calculation methods
         */
        setupCalculationMethods() {
            this.calculationMethods.set(SCORING_CONSTANTS.CALCULATION_METHODS.AVERAGE, this.calculateAverage.bind(this));
            this.calculationMethods.set(SCORING_CONSTANTS.CALCULATION_METHODS.WEIGHTED_AVERAGE, this.calculateWeightedAverage.bind(this));
            this.calculationMethods.set(SCORING_CONSTANTS.CALCULATION_METHODS.SUM, this.calculateSum.bind(this));
            this.calculationMethods.set(SCORING_CONSTANTS.CALCULATION_METHODS.HIGHEST, this.calculateHighest.bind(this));
            this.calculationMethods.set(SCORING_CONSTANTS.CALCULATION_METHODS.LOWEST, this.calculateLowest.bind(this));
        }

        /**
         * Calculate score based on method
         */
        calculate(scores, method = SCORING_CONSTANTS.CALCULATION_METHODS.AVERAGE, weights = null) {
            const calculator = this.calculationMethods.get(method);
            if (!calculator) {
                throw new Error(`Calculation method '${method}' not supported`);
            }

            return calculator(scores, weights);
        }

        /**
         * Calculate average
         */
        calculateAverage(scores) {
            if (!scores || scores.length === 0) return 0;
            
            const validScores = scores.filter(score => 
                score !== null && score !== undefined && !isNaN(score)
            );
            
            if (validScores.length === 0) return 0;
            
            const sum = validScores.reduce((acc, score) => acc + parseFloat(score), 0);
            return sum / validScores.length;
        }

        /**
         * Calculate weighted average
         */
        calculateWeightedAverage(scores, weights) {
            if (!scores || scores.length === 0) return 0;
            if (!weights || weights.length !== scores.length) {
                return this.calculateAverage(scores);
            }

            let weightedSum = 0;
            let totalWeight = 0;

            for (let i = 0; i < scores.length; i++) {
                if (scores[i] !== null && scores[i] !== undefined && !isNaN(scores[i])) {
                    weightedSum += parseFloat(scores[i]) * weights[i];
                    totalWeight += weights[i];
                }
            }

            return totalWeight > 0 ? weightedSum / totalWeight : 0;
        }

        /**
         * Calculate sum
         */
        calculateSum(scores) {
            if (!scores || scores.length === 0) return 0;
            
            const validScores = scores.filter(score => 
                score !== null && score !== undefined && !isNaN(score)
            );
            
            return validScores.reduce((acc, score) => acc + parseFloat(score), 0);
        }

        /**
         * Calculate highest score
         */
        calculateHighest(scores) {
            if (!scores || scores.length === 0) return 0;
            
            const validScores = scores.filter(score => 
                score !== null && score !== undefined && !isNaN(score)
            );
            
            return validScores.length > 0 ? Math.max(...validScores) : 0;
        }

        /**
         * Calculate lowest score
         */
        calculateLowest(scores) {
            if (!scores || scores.length === 0) return 0;
            
            const validScores = scores.filter(score => 
                score !== null && score !== undefined && !isNaN(score)
            );
            
            return validScores.length > 0 ? Math.min(...validScores) : 0;
        }

        /**
         * Convert descriptive score to numeric
         */
        convertDescriptiveToNumeric(descriptiveScore) {
            const mapping = {
                'Sangat Buruk': 20,
                'Buruk': 40,
                'Cukup': 60,
                'Baik': 80,
                'Sangat Baik': 100
            };

            return mapping[descriptiveScore] || 0;
        }

        /**
         * Convert numeric score to descriptive
         */
        convertNumericToDescriptive(numericScore) {
            if (numericScore >= 90) return 'Sangat Baik';
            if (numericScore >= 80) return 'Baik';
            if (numericScore >= 70) return 'Cukup';
            if (numericScore >= 60) return 'Kurang';
            return 'Sangat Kurang';
        }

        /**
         * Get performance category
         */
        getPerformanceCategory(score) {
            const categories = SCORING_CONSTANTS.PERFORMANCE_CATEGORIES;
            
            for (const [key, category] of Object.entries(categories)) {
                if (score >= category.min && score <= category.max) {
                    return {
                        key: key,
                        label: category.label,
                        color: category.color,
                        min: category.min,
                        max: category.max
                    };
                }
            }

            return null;
        }
    }

    /**
     * Assessment Validator
     */
    class AssessmentValidator {
        constructor() {
            this.validationRules = new Map();
            this.setupValidationRules();
        }

        /**
         * Setup validation rules
         */
        setupValidationRules() {
            this.validationRules.set(SCORING_CONSTANTS.SCORING_TYPES.NUMERIC, this.validateNumericScore.bind(this));
            this.validationRules.set(SCORING_CONSTANTS.SCORING_TYPES.DESCRIPTIVE, this.validateDescriptiveScore.bind(this));
            this.validationRules.set(SCORING_CONSTANTS.SCORING_TYPES.BINARY, this.validateBinaryScore.bind(this));
            this.validationRules.set(SCORING_CONSTANTS.SCORING_TYPES.MULTIPLE_CHOICE, this.validateMultipleChoiceScore.bind(this));
            this.validationRules.set(SCORING_CONSTANTS.SCORING_TYPES.WEIGHTED, this.validateWeightedScore.bind(this));
            this.validationRules.set(SCORING_CONSTANTS.SCORING_TYPES.RUBRIC, this.validateRubricScore.bind(this));
        }

        /**
         * Validate score based on type
         */
        validateScore(score, scoringType, options = {}) {
            const validator = this.validationRules.get(scoringType);
            if (!validator) {
                return {
                    valid: false,
                    errors: [`Validation not supported for scoring type: ${scoringType}`]
                };
            }

            return validator(score, options);
        }

        /**
         * Validate numeric score
         */
        validateNumericScore(score, options = {}) {
            const errors = [];
            const min = options.min || SCORING_CONSTANTS.SCORE_RANGES.NUMERIC.min;
            const max = options.max || SCORING_CONSTANTS.SCORE_RANGES.NUMERIC.max;

            if (score === null || score === undefined || score === '') {
                errors.push(SCORING_CONSTANTS.ERROR_MESSAGES.INVALID_SCORE);
            } else if (isNaN(score)) {
                errors.push('Nilai harus berupa angka');
            } else if (parseFloat(score) < min || parseFloat(score) > max) {
                errors.push(`${SCORING_CONSTANTS.ERROR_MESSAGES.OUT_OF_RANGE} (${min}-${max})`);
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate descriptive score
         */
        validateDescriptiveScore(score, options = {}) {
            const validDescriptions = options.validDescriptions || SCORING_CONSTANTS.SCORE_RANGES.DESCRIPTIVE;
            const errors = [];

            if (!score || score.trim() === '') {
                errors.push(SCORING_CONSTANTS.ERROR_MESSAGES.INVALID_SCORE);
            } else if (!validDescriptions.includes(score)) {
                errors.push(`Deskripsi tidak valid. Pilihan: ${validDescriptions.join(', ')}`);
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate binary score
         */
        validateBinaryScore(score, options = {}) {
            const errors = [];
            const validValues = SCORING_CONSTANTS.SCORE_RANGES.BINARY;

            if (score === null || score === undefined) {
                errors.push(SCORING_CONSTANTS.ERROR_MESSAGES.INVALID_SCORE);
            } else if (!validValues.includes(parseInt(score))) {
                errors.push('Nilai harus 0 atau 1');
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate multiple choice score
         */
        validateMultipleChoiceScore(score, options = {}) {
            const errors = [];

            if (!score || score.trim() === '') {
                errors.push(SCORING_CONSTANTS.ERROR_MESSAGES.INVALID_SCORE);
            } else if (options.validOptions && !options.validOptions.includes(score)) {
                errors.push(`Pilihan tidak valid. Pilihan: ${options.validOptions.join(', ')}`);
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate weighted score
         */
        validateWeightedScore(score, options = {}) {
            const errors = [];
            const weights = options.weights;

            if (!weights || !Array.isArray(weights)) {
                errors.push('Bobot tidak valid');
            } else {
                const totalWeight = weights.reduce((sum, weight) => sum + weight, 0);
                if (Math.abs(totalWeight - 100) > 0.01) {
                    errors.push('Total bobot harus 100%');
                }
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate rubric score
         */
        validateRubricScore(score, options = {}) {
            const errors = [];
            const rubric = options.rubric;

            if (!rubric || !Array.isArray(rubric.criteria)) {
                errors.push('Rubrik tidak valid');
            } else {
                // Validate that all criteria have scores
                const incompleteCriteria = rubric.criteria.filter(criterion => 
                    criterion.score === null || criterion.score === undefined
                );

                if (incompleteCriteria.length > 0) {
                    errors.push('Semua kriteria rubrik harus dinilai');
                }
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate evidence
         */
        validateEvidence(evidence, options = {}) {
            const errors = [];

            if (options.required && (!evidence || evidence.trim() === '')) {
                errors.push(SCORING_CONSTANTS.ERROR_MESSAGES.MISSING_EVIDENCE);
            }

            if (evidence && options.minLength && evidence.length < options.minLength) {
                errors.push(`Bukti pendukung minimal ${options.minLength} karakter`);
            }

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate assessment completeness
         */
        validateAssessmentCompleteness(assessment, options = {}) {
            const errors = [];
            const requiredFields = options.requiredFields || ['scores', 'evidence', 'comments'];

            requiredFields.forEach(field => {
                if (!assessment[field]) {
                    errors.push(`${SCORING_CONSTANTS.ERROR_MESSAGES.INCOMPLETE_ASSESSMENT}: ${field}`);
                }
            });

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }
    }

    /**
     * Assessment Scoring Manager
     */
    class AssessmentScoringManager {
        constructor() {
            this.calculator = new ScoreCalculator();
            this.validator = new AssessmentValidator();
            this.assessments = new Map();
            this.scoringHistory = [];
        }

        /**
         * Initialize assessment scoring
         */
        initialize(options = {}) {
            this.options = {
                autoSave: options.autoSave !== false,
                autoCalculate: options.autoCalculate !== false,
                validationEnabled: options.validationEnabled !== false,
                evidenceRequired: options.evidenceRequired !== false,
                minEvidenceLength: options.minEvidenceLength || 10,
                ...options
            };

            return this;
        }

        /**
         * Create new assessment
         */
        createAssessment(assessmentConfig) {
            const assessmentId = assessmentConfig.id || `assessment_${Date.now()}`;
            
            const assessment = {
                id: assessmentId,
                institutionId: assessmentConfig.institutionId,
                year: assessmentConfig.year,
                indicatorId: assessmentConfig.indicatorId,
                status: SCORING_CONSTANTS.ASSESSMENT_STATUS.DRAFT,
                scores: new Map(),
                evidence: new Map(),
                comments: new Map(),
                weights: assessmentConfig.weights || new Map(),
                scoringType: assessmentConfig.scoringType || SCORING_CONSTANTS.SCORING_TYPES.NUMERIC,
                calculationMethod: assessmentConfig.calculationMethod || SCORING_CONSTANTS.CALCULATION_METHODS.AVERAGE,
                createdAt: new Date(),
                updatedAt: new Date(),
                createdBy: assessmentConfig.createdBy,
                totalScore: 0,
                performanceCategory: null
            };

            this.assessments.set(assessmentId, assessment);
            return assessment;
        }

        /**
         * Add score to assessment
         */
        addScore(assessmentId, criterionId, score, options = {}) {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            // Validate score
            if (this.options.validationEnabled) {
                const validationResult = this.validator.validateScore(score, assessment.scoringType, options);
                if (!validationResult.valid) {
                    throw new Error(`Validation failed: ${validationResult.errors.join(', ')}`);
                }
            }

            // Add score
            assessment.scores.set(criterionId, {
                score: score,
                timestamp: new Date(),
                options: options
            });

            // Add evidence if provided
            if (options.evidence) {
                this.addEvidence(assessmentId, criterionId, options.evidence, options);
            }

            // Add comment if provided
            if (options.comment) {
                this.addComment(assessmentId, criterionId, options.comment);
            }

            // Update assessment
            assessment.updatedAt = new Date();
            
            // Auto-calculate total score
            if (this.options.autoCalculate) {
                this.calculateTotalScore(assessmentId);
            }

            // Auto-save if enabled
            if (this.options.autoSave) {
                this.saveAssessment(assessmentId);
            }

            // Add to history
            this.scoringHistory.push({
                assessmentId: assessmentId,
                criterionId: criterionId,
                score: score,
                action: 'add_score',
                timestamp: new Date()
            });

            return assessment;
        }

        /**
         * Add evidence
         */
        addEvidence(assessmentId, criterionId, evidence, options = {}) {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            // Validate evidence
            if (this.options.validationEnabled) {
                const validationResult = this.validator.validateEvidence(evidence, {
                    required: this.options.evidenceRequired,
                    minLength: this.options.minEvidenceLength
                });

                if (!validationResult.valid) {
                    throw new Error(`Evidence validation failed: ${validationResult.errors.join(', ')}`);
                }
            }

            // Add evidence
            assessment.evidence.set(criterionId, {
                evidence: evidence,
                timestamp: new Date(),
                options: options
            });

            assessment.updatedAt = new Date();
            return assessment;
        }

        /**
         * Add comment
         */
        addComment(assessmentId, criterionId, comment, options = {}) {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            assessment.comments.set(criterionId, {
                comment: comment,
                timestamp: new Date(),
                options: options
            });

            assessment.updatedAt = new Date();
            return assessment;
        }

        /**
         * Calculate total score
         */
        calculateTotalScore(assessmentId) {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            const scores = Array.from(assessment.scores.values()).map(item => item.score);
            const weights = Array.from(assessment.weights.values());

            // Calculate total score
            assessment.totalScore = this.calculator.calculate(scores, assessment.calculationMethod, weights);
            
            // Update performance category
            assessment.performanceCategory = this.calculator.getPerformanceCategory(assessment.totalScore);

            return assessment;
        }

        /**
         * Submit assessment
         */
        async submitAssessment(assessmentId, options = {}) {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            // Validate assessment completeness
            if (this.options.validationEnabled) {
                const validationResult = this.validator.validateAssessmentCompleteness(assessment, {
                    requiredFields: ['scores', 'evidence']
                });

                if (!validationResult.valid) {
                    throw new Error(`Assessment validation failed: ${validationResult.errors.join(', ')}`);
                }
            }

            // Update status
            assessment.status = SCORING_CONSTANTS.ASSESSMENT_STATUS.SUBMITTED;
            assessment.submittedAt = new Date();
            assessment.submittedBy = options.userId;

            // Save assessment
            await this.saveAssessment(assessmentId);

            // Add to history
            this.scoringHistory.push({
                assessmentId: assessmentId,
                action: 'submit_assessment',
                timestamp: new Date()
            });

            return assessment;
        }

        /**
         * Approve assessment
         */
        async approveAssessment(assessmentId, approverId, comments = '') {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            if (assessment.status !== SCORING_CONSTANTS.ASSESSMENT_STATUS.SUBMITTED) {
                throw new Error('Assessment must be submitted before approval');
            }

            assessment.status = SCORING_CONSTANTS.ASSESSMENT_STATUS.APPROVED;
            assessment.approvedAt = new Date();
            assessment.approvedBy = approverId;
            assessment.approvalComments = comments;

            await this.saveAssessment(assessmentId);

            this.scoringHistory.push({
                assessmentId: assessmentId,
                action: 'approve_assessment',
                timestamp: new Date()
            });

            return assessment;
        }

        /**
         * Reject assessment
         */
        async rejectAssessment(assessmentId, approverId, comments = '') {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            if (assessment.status !== SCORING_CONSTANTS.ASSESSMENT_STATUS.SUBMITTED) {
                throw new Error('Assessment must be submitted before rejection');
            }

            assessment.status = SCORING_CONSTANTS.ASSESSMENT_STATUS.REJECTED;
            assessment.rejectedAt = new Date();
            assessment.rejectedBy = approverId;
            assessment.rejectionComments = comments;

            await this.saveAssessment(assessmentId);

            this.scoringHistory.push({
                assessmentId: assessmentId,
                action: 'reject_assessment',
                timestamp: new Date()
            });

            return assessment;
        }

        /**
         * Save assessment (placeholder for API call)
         */
        async saveAssessment(assessmentId) {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            // Convert Maps to Objects for serialization
            const assessmentData = {
                ...assessment,
                scores: Object.fromEntries(assessment.scores),
                evidence: Object.fromEntries(assessment.evidence),
                comments: Object.fromEntries(assessment.comments),
                weights: Object.fromEntries(assessment.weights)
            };

            // Simulate API call
            console.log('Saving assessment:', assessmentData);
            
            // In real implementation, this would be an API call
            // const response = await fetch('/api/assessments', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(assessmentData)
            // });
            
            return assessment;
        }

        /**
         * Get assessment
         */
        getAssessment(assessmentId) {
            return this.assessments.get(assessmentId);
        }

        /**
         * Get all assessments
         */
        getAllAssessments() {
            return Array.from(this.assessments.values());
        }

        /**
         * Get assessments by status
         */
        getAssessmentsByStatus(status) {
            return Array.from(this.assessments.values()).filter(assessment => 
                assessment.status === status
            );
        }

        /**
         * Get scoring history
         */
        getScoringHistory(assessmentId = null) {
            if (assessmentId) {
                return this.scoringHistory.filter(entry => entry.assessmentId === assessmentId);
            }
            return this.scoringHistory;
        }

        /**
         * Delete assessment
         */
        deleteAssessment(assessmentId) {
            const assessment = this.assessments.get(assessmentId);
            if (!assessment) {
                throw new Error(`Assessment with ID '${assessmentId}' not found`);
            }

            if (assessment.status === SCORING_CONSTANTS.ASSESSMENT_STATUS.APPROVED) {
                throw new Error('Cannot delete approved assessment');
            }

            this.assessments.delete(assessmentId);
            
            this.scoringHistory.push({
                assessmentId: assessmentId,
                action: 'delete_assessment',
                timestamp: new Date()
            });

            return true;
        }
    }

    /**
     * Assessment Scoring UI Manager
     */
    class AssessmentScoringUIManager {
        constructor() {
            this.activeComponents = new Map();
            this.scoringManager = new AssessmentScoringManager();
        }

        /**
         * Create scoring interface
         */
        createScoringInterface(containerId, assessmentConfig) {
            const container = document.getElementById(containerId);
            if (!container) {
                throw new Error(`Container with ID '${containerId}' not found`);
            }

            const scoringInterface = new ScoringInterface(container, assessmentConfig, this.scoringManager);
            this.activeComponents.set(containerId, scoringInterface);
            
            return scoringInterface;
        }

        /**
         * Create rubric interface
         */
        createRubricInterface(containerId, rubricConfig) {
            const container = document.getElementById(containerId);
            if (!container) {
                throw new Error(`Container with ID '${containerId}' not found`);
            }

            const rubricInterface = new RubricInterface(container, rubricConfig, this.scoringManager);
            this.activeComponents.set(containerId, rubricInterface);
            
            return rubricInterface;
        }

        /**
         * Destroy component
         */
        destroyComponent(containerId) {
            const component = this.activeComponents.get(containerId);
            if (component) {
                component.destroy();
                this.activeComponents.delete(containerId);
            }
        }
    }

    /**
     * Scoring Interface Component
     */
    class ScoringInterface {
        constructor(container, assessmentConfig, scoringManager) {
            this.container = container;
            this.assessmentConfig = assessmentConfig;
            this.scoringManager = scoringManager;
            this.assessment = null;
            this.setupUI();
            this.initializeAssessment();
        }

        /**
         * Setup UI
         */
        setupUI() {
            this.container.innerHTML = `
                <div class="sakip-scoring-container">
                    <div class="sakip-scoring-header">
                        <h3>Penilaian SAKIP</h3>
                        <div class="sakip-scoring-info">
                            <span class="sakip-institution-name">${this.assessmentConfig.institutionName || ''}</span>
                            <span class="sakip-assessment-year">Tahun ${this.assessmentConfig.year || ''}</span>
                        </div>
                    </div>
                    
                    <div class="sakip-scoring-criteria" id="scoring-criteria">
                        <!-- Criteria will be populated here -->
                    </div>
                    
                    <div class="sakip-scoring-summary" id="scoring-summary">
                        <div class="sakip-total-score">
                            <span class="sakip-score-label">Total Nilai:</span>
                            <span class="sakip-score-value" id="total-score">0</span>
                        </div>
                        <div class="sakip-performance-category" id="performance-category">
                            <span class="sakip-category-label">Kategori:</span>
                            <span class="sakip-category-value" id="category-value">Belum Dinilai</span>
                        </div>
                    </div>
                    
                    <div class="sakip-scoring-actions">
                        <button type="button" class="sakip-btn sakip-btn-secondary" id="save-draft">
                            <i class="fas fa-save"></i> Simpan Draft
                        </button>
                        <button type="button" class="sakip-btn sakip-btn-primary" id="submit-assessment">
                            <i class="fas fa-paper-plane"></i> Submit Penilaian
                        </button>
                    </div>
                </div>
            `;

            this.setupEventListeners();
        }

        /**
         * Initialize assessment
         */
        initializeAssessment() {
            this.assessment = this.scoringManager.createAssessment({
                institutionId: this.assessmentConfig.institutionId,
                year: this.assessmentConfig.year,
                indicatorId: this.assessmentConfig.indicatorId,
                scoringType: this.assessmentConfig.scoringType,
                calculationMethod: this.assessmentConfig.calculationMethod,
                createdBy: this.assessmentConfig.userId
            });

            this.populateCriteria();
        }

        /**
         * Populate criteria
         */
        populateCriteria() {
            const criteriaContainer = this.container.querySelector('#scoring-criteria');
            const criteria = this.assessmentConfig.criteria || [];

            criteria.forEach((criterion, index) => {
                const criterionElement = this.createCriterionElement(criterion, index);
                criteriaContainer.appendChild(criterionElement);
            });
        }

        /**
         * Create criterion element
         */
        createCriterionElement(criterion, index) {
            const criterionDiv = document.createElement('div');
            criterionDiv.className = 'sakip-criterion-item';
            criterionDiv.dataset.criterionId = criterion.id;

            criterionDiv.innerHTML = `
                <div class="sakip-criterion-header">
                    <h4>${criterion.name}</h4>
                    <span class="sakip-criterion-weight">Bobot: ${criterion.weight || 0}%</span>
                </div>
                <div class="sakip-criterion-description">
                    <p>${criterion.description || ''}</p>
                </div>
                <div class="sakip-criterion-scoring">
                    <div class="sakip-score-input">
                        <label>Nilai:</label>
                        <input type="number" class="sakip-score-field" 
                               id="score-${criterion.id}" 
                               min="0" max="100" step="0.1"
                               data-criterion-id="${criterion.id}"
                               data-weight="${criterion.weight || 0}">
                        <span class="sakip-score-hint">0-100</span>
                    </div>
                    <div class="sakip-evidence-input">
                        <label>Bukti Pendukung:</label>
                        <textarea class="sakip-evidence-field" 
                                  id="evidence-${criterion.id}"
                                  data-criterion-id="${criterion.id}"
                                  placeholder="Masukkan bukti pendukung..."></textarea>
                    </div>
                    <div class="sakip-comment-input">
                        <label>Komentar:</label>
                        <textarea class="sakip-comment-field" 
                                  id="comment-${criterion.id}"
                                  data-criterion-id="${criterion.id}"
                                  placeholder="Masukkan komentar atau catatan..."></textarea>
                    </div>
                </div>
                <div class="sakip-criterion-validation" id="validation-${criterion.id}"></div>
            `;

            return criterionDiv;
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Score input changes
            this.container.addEventListener('input', (e) => {
                if (e.target.classList.contains('sakip-score-field')) {
                    this.handleScoreChange(e.target);
                }
            });

            // Evidence input changes
            this.container.addEventListener('input', (e) => {
                if (e.target.classList.contains('sakip-evidence-field')) {
                    this.handleEvidenceChange(e.target);
                }
            });

            // Comment input changes
            this.container.addEventListener('input', (e) => {
                if (e.target.classList.contains('sakip-comment-field')) {
                    this.handleCommentChange(e.target);
                }
            });

            // Action buttons
            this.container.addEventListener('click', (e) => {
                if (e.target.id === 'save-draft') {
                    this.saveDraft();
                } else if (e.target.id === 'submit-assessment') {
                    this.submitAssessment();
                }
            });
        }

        /**
         * Handle score change
         */
        handleScoreChange(scoreField) {
            const criterionId = scoreField.dataset.criterionId;
            const score = parseFloat(scoreField.value) || 0;
            const weight = parseFloat(scoreField.dataset.weight) || 0;

            try {
                this.scoringManager.addScore(this.assessment.id, criterionId, score, {
                    weight: weight
                });
                this.clearValidationError(criterionId);
                this.updateSummary();
            } catch (error) {
                this.showValidationError(criterionId, error.message);
            }
        }

        /**
         * Handle evidence change
         */
        handleEvidenceChange(evidenceField) {
            const criterionId = evidenceField.dataset.criterionId;
            const evidence = evidenceField.value;

            try {
                this.scoringManager.addEvidence(this.assessment.id, criterionId, evidence);
                this.clearValidationError(criterionId);
            } catch (error) {
                this.showValidationError(criterionId, error.message);
            }
        }

        /**
         * Handle comment change
         */
        handleCommentChange(commentField) {
            const criterionId = commentField.dataset.criterionId;
            const comment = commentField.value;

            this.scoringManager.addComment(this.assessment.id, criterionId, comment);
        }

        /**
         * Update summary
         */
        updateSummary() {
            const totalScoreElement = this.container.querySelector('#total-score');
            const categoryElement = this.container.querySelector('#category-value');

            const totalScore = this.assessment.totalScore || 0;
            const performanceCategory = this.assessment.performanceCategory;

            totalScoreElement.textContent = totalScore.toFixed(2);

            if (performanceCategory) {
                categoryElement.textContent = performanceCategory.label;
                categoryElement.style.color = performanceCategory.color;
            } else {
                categoryElement.textContent = 'Belum Dinilai';
                categoryElement.style.color = '#6c757d';
            }
        }

        /**
         * Show validation error
         */
        showValidationError(criterionId, message) {
            const validationElement = this.container.querySelector(`#validation-${criterionId}`);
            validationElement.innerHTML = `<div class="sakip-validation-error">${message}</div>`;
            validationElement.style.display = 'block';
        }

        /**
         * Clear validation error
         */
        clearValidationError(criterionId) {
            const validationElement = this.container.querySelector(`#validation-${criterionId}`);
            validationElement.innerHTML = '';
            validationElement.style.display = 'none';
        }

        /**
         * Save draft
         */
        async saveDraft() {
            try {
                await this.scoringManager.saveAssessment(this.assessment.id);
                this.showSuccessMessage('Draft berhasil disimpan');
            } catch (error) {
                this.showErrorMessage('Gagal menyimpan draft: ' + error.message);
            }
        }

        /**
         * Submit assessment
         */
        async submitAssessment() {
            try {
                await this.scoringManager.submitAssessment(this.assessment.id, {
                    userId: this.assessmentConfig.userId
                });
                this.showSuccessMessage('Penilaian berhasil disubmit');
                
                // Disable form inputs
                this.container.querySelectorAll('input, textarea').forEach(element => {
                    element.disabled = true;
                });
                
                this.container.querySelector('#submit-assessment').disabled = true;
            } catch (error) {
                this.showErrorMessage('Gagal submit penilaian: ' + error.message);
            }
        }

        /**
         * Show success message
         */
        showSuccessMessage(message) {
            const alert = document.createElement('div');
            alert.className = 'sakip-alert sakip-alert-success';
            alert.textContent = message;
            
            this.container.insertBefore(alert, this.container.firstChild);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        /**
         * Show error message
         */
        showErrorMessage(message) {
            const alert = document.createElement('div');
            alert.className = 'sakip-alert sakip-alert-error';
            alert.textContent = message;
            
            this.container.insertBefore(alert, this.container.firstChild);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        /**
         * Destroy interface
         */
        destroy() {
            this.container.innerHTML = '';
        }
    }

    /**
     * Main SAKIP Assessment Scoring API
     */
    const SAKIP_ASSESSMENT_SCORING = {
        // Constants
        constants: SCORING_CONSTANTS,

        // Core classes
        ScoreCalculator,
        AssessmentValidator,
        AssessmentScoringManager,
        AssessmentScoringUIManager,
        ScoringInterface,

        // Create instances
        calculator: new ScoreCalculator(),
        validator: new AssessmentValidator(),
        manager: new AssessmentScoringManager(),
        uiManager: new AssessmentScoringUIManager(),

        // Convenience methods
        initialize: (options) => manager.initialize(options),
        createAssessment: (config) => manager.createAssessment(config),
        addScore: (assessmentId, criterionId, score, options) => manager.addScore(assessmentId, criterionId, score, options),
        addEvidence: (assessmentId, criterionId, evidence, options) => manager.addEvidence(assessmentId, criterionId, evidence, options),
        addComment: (assessmentId, criterionId, comment, options) => manager.addComment(assessmentId, criterionId, comment, options),
        calculateTotalScore: (assessmentId) => manager.calculateTotalScore(assessmentId),
        submitAssessment: (assessmentId, options) => manager.submitAssessment(assessmentId, options),
        approveAssessment: (assessmentId, approverId, comments) => manager.approveAssessment(assessmentId, approverId, comments),
        rejectAssessment: (assessmentId, rejecterId, comments) => manager.rejectAssessment(assessmentId, rejecterId, comments),
        getAssessment: (assessmentId) => manager.getAssessment(assessmentId),
        getAllAssessments: () => manager.getAllAssessments(),
        getAssessmentsByStatus: (status) => manager.getAssessmentsByStatus(status),
        deleteAssessment: (assessmentId) => manager.deleteAssessment(assessmentId),
        getScoringHistory: (assessmentId) => manager.getScoringHistory(assessmentId),

        // UI methods
        createScoringInterface: (containerId, config) => uiManager.createScoringInterface(containerId, config),
        createRubricInterface: (containerId, config) => uiManager.createRubricInterface(containerId, config),
        destroyComponent: (containerId) => uiManager.destroyComponent(containerId),

        // Validation methods
        validateScore: (score, scoringType, options) => validator.validateScore(score, scoringType, options),
        validateEvidence: (evidence, options) => validator.validateEvidence(evidence, options),
        validateAssessmentCompleteness: (assessment, options) => validator.validateAssessmentCompleteness(assessment, options),

        // Calculation methods
        calculate: (scores, method, weights) => calculator.calculate(scores, method, weights),
        convertDescriptiveToNumeric: (descriptiveScore) => calculator.convertDescriptiveToNumeric(descriptiveScore),
        convertNumericToDescriptive: (numericScore) => calculator.convertNumericToDescriptive(numericScore),
        getPerformanceCategory: (score) => calculator.getPerformanceCategory(score)
    };

    return SAKIP_ASSESSMENT_SCORING;
}));