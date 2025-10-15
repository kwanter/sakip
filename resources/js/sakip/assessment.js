/**
 * SAKIP Assessment JavaScript Module
 * Handles assessment scoring, real-time calculations, and workflow
 */

class SakipAssessment {
    constructor() {
        this.currentScore = 0;
        this.scoringCriteria = {};
        this.init();
    }

    /**
     * Initialize assessment functionality
     */
    init() {
        this.initializeScoringSystem();
        this.setupRealTimeCalculations();
        this.setupWorkflowHandlers();
        this.initializeAssessmentForms();
    }

    /**
     * Initialize scoring system
     */
    initializeScoringSystem() {
        const scoreInputs = document.querySelectorAll('.assessment-score');
        
        scoreInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.updateScore(e.target);
            });
            
            input.addEventListener('blur', (e) => {
                this.validateScore(e.target);
            });
        });
        
        // Initialize with existing values
        this.calculateTotalScore();
    }

    /**
     * Update score for individual criterion
     */
    updateScore(scoreInput) {
        const criterionId = scoreInput.dataset.criterionId;
        const maxScore = parseFloat(scoreInput.dataset.maxScore || '0');
        const score = parseFloat(scoreInput.value || '0');
        
        // Validate score range
        if (score < 0 || score > maxScore) {
            this.showScoreError(scoreInput, `Nilai harus antara 0 dan ${maxScore}`);
            return;
        }
        
        this.clearScoreError(scoreInput);
        
        // Update visual indicator
        this.updateScoreIndicator(scoreInput, score, maxScore);
        
        // Update weight calculation
        const weight = parseFloat(scoreInput.dataset.weight || '0');
        const weightedScore = (score / maxScore) * weight;
        
        // Update weighted score display
        const weightedDisplay = scoreInput.closest('.criterion-item')
            .querySelector('.weighted-score');
        if (weightedDisplay) {
            weightedDisplay.textContent = weightedScore.toFixed(2);
        }
        
        // Recalculate total score
        this.calculateTotalScore();
        
        // Update recommendation based on score
        this.updateRecommendation(criterionId, score, maxScore);
    }

    /**
     * Update score visual indicator
     */
    updateScoreIndicator(scoreInput, score, maxScore) {
        const percentage = (score / maxScore) * 100;
        const indicator = scoreInput.closest('.criterion-item')
            .querySelector('.score-indicator');
        
        if (!indicator) return;
        
        let colorClass = 'bg-danger';
        let text = 'Buruk';
        
        if (percentage >= 80) {
            colorClass = 'bg-success';
            text = 'Baik Sekali';
        } else if (percentage >= 60) {
            colorClass = 'bg-warning';
            text = 'Cukup';
        } else if (percentage >= 40) {
            colorClass = 'bg-orange';
            text = 'Kurang';
        }
        
        indicator.className = `score-indicator ${colorClass}`;
        indicator.textContent = text;
        indicator.style.width = `${percentage}%`;
    }

    /**
     * Calculate total assessment score
     */
    calculateTotalScore() {
        let totalScore = 0;
        let totalWeight = 0;
        
        const scoreInputs = document.querySelectorAll('.assessment-score');
        
        scoreInputs.forEach(input => {
            const score = parseFloat(input.value || '0');
            const maxScore = parseFloat(input.dataset.maxScore || '0');
            const weight = parseFloat(input.dataset.weight || '0');
            
            if (maxScore > 0 && weight > 0) {
                const normalizedScore = (score / maxScore) * 100;
                totalScore += normalizedScore * weight;
                totalWeight += weight;
            }
        });
        
        const finalScore = totalWeight > 0 ? totalScore / totalWeight : 0;
        
        // Update total score display
        const totalScoreElement = document.getElementById('totalAssessmentScore');
        if (totalScoreElement) {
            totalScoreElement.textContent = finalScore.toFixed(2);
            this.updateScoreColor(totalScoreElement, finalScore);
        }
        
        // Update grade display
        this.updateGradeDisplay(finalScore);
        
        // Update performance category
        this.updatePerformanceCategory(finalScore);
        
        this.currentScore = finalScore;
    }

    /**
     * Update score color based on value
     */
    updateScoreColor(element, score) {
        element.className = element.className.replace(/text-\w+-500/g, '');
        
        if (score >= 80) {
            element.classList.add('text-green-500');
        } else if (score >= 60) {
            element.classList.add('text-yellow-500');
        } else if (score >= 40) {
            element.classList.add('text-orange-500');
        } else {
            element.classList.add('text-red-500');
        }
    }

    /**
     * Update grade display
     */
    updateGradeDisplay(score) {
        const gradeElement = document.getElementById('assessmentGrade');
        if (!gradeElement) return;
        
        let grade = 'E';
        let gradeClass = 'text-red-500';
        
        if (score >= 90) {
            grade = 'A';
            gradeClass = 'text-green-500';
        } else if (score >= 80) {
            grade = 'B';
            gradeClass = 'text-green-400';
        } else if (score >= 70) {
            grade = 'C';
            gradeClass = 'text-yellow-500';
        } else if (score >= 60) {
            grade = 'D';
            gradeClass = 'text-orange-500';
        }
        
        gradeElement.textContent = grade;
        gradeElement.className = gradeElement.className.replace(/text-\w+-500/g, '');
        gradeElement.classList.add(gradeClass);
    }

    /**
     * Update performance category
     */
    updatePerformanceCategory(score) {
        const categoryElement = document.getElementById('performanceCategory');
        if (!categoryElement) return;
        
        let category = 'Tidak Memenuhi Standar';
        let categoryClass = 'text-red-500';
        
        if (score >= 80) {
            category = 'Sangat Memenuhi Standar';
            categoryClass = 'text-green-500';
        } else if (score >= 60) {
            category = 'Memenuhi Standar';
            categoryClass = 'text-yellow-500';
        } else if (score >= 40) {
            category = 'Kurang Memenuhi Standar';
            categoryClass = 'text-orange-500';
        }
        
        categoryElement.textContent = category;
        categoryElement.className = categoryElement.className.replace(/text-\w+-500/g, '');
        categoryElement.classList.add(categoryClass);
    }

    /**
     * Validate score input
     */
    validateScore(scoreInput) {
        const score = parseFloat(scoreInput.value || '0');
        const maxScore = parseFloat(scoreInput.dataset.maxScore || '0');
        
        if (isNaN(score)) {
            this.showScoreError(scoreInput, 'Nilai harus berupa angka');
            return false;
        }
        
        if (score < 0 || score > maxScore) {
            this.showScoreError(scoreInput, `Nilai harus antara 0 dan ${maxScore}`);
            return false;
        }
        
        this.clearScoreError(scoreInput);
        return true;
    }

    /**
     * Show score error
     */
    showScoreError(scoreInput, message) {
        const errorElement = scoreInput.parentElement.querySelector('.score-error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        
        scoreInput.classList.add('is-invalid');
    }

    /**
     * Clear score error
     */
    clearScoreError(scoreInput) {
        const errorElement = scoreInput.parentElement.querySelector('.score-error');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
        
        scoreInput.classList.remove('is-invalid');
    }

    /**
     * Update recommendation based on score
     */
    updateRecommendation(criterionId, score, maxScore) {
        const percentage = (score / maxScore) * 100;
        const recommendationElement = document.getElementById(`recommendation-${criterionId}`);
        
        if (!recommendationElement) return;
        
        let recommendation = '';
        
        if (percentage >= 80) {
            recommendation = 'Pertahankan kinerja yang baik ini. Terus tingkatkan inovasi dan efisiensi.';
        } else if (percentage >= 60) {
            recommendation = 'Tingkatkan kinerja dengan memperbaiki proses dan alokasi sumber daya.';
        } else if (percentage >= 40) {
            recommendation = 'Perlu perhatian serius. Evaluasi ulang strategi dan implementasi program.';
        } else {
            recommendation = 'Sangat memerlukan perbaikan mendasar. Lakukan evaluasi komprehensif.';
        }
        
        recommendationElement.textContent = recommendation;
    }

    /**
     * Setup real-time calculations
     */
    setupRealTimeCalculations() {
        // Auto-calculate when data changes
        const dataInputs = document.querySelectorAll('.assessment-data-input');
        
        dataInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.performRealTimeCalculation(input);
            });
        });
    }

    /**
     * Perform real-time calculation
     */
    performRealTimeCalculation(input) {
        const calculationType = input.dataset.calculationType;
        
        switch (calculationType) {
            case 'percentage':
                this.calculatePercentage(input);
                break;
            case 'ratio':
                this.calculateRatio(input);
                break;
            case 'average':
                this.calculateAverage(input);
                break;
            case 'total':
                this.calculateTotal(input);
                break;
        }
    }

    /**
     * Calculate percentage
     */
    calculatePercentage(input) {
        const numerator = parseFloat(input.value || '0');
        const denominatorInput = document.querySelector(input.dataset.denominator);
        const denominator = parseFloat(denominatorInput?.value || '0');
        
        if (denominator > 0) {
            const percentage = (numerator / denominator) * 100;
            const resultElement = document.querySelector(input.dataset.result);
            if (resultElement) {
                resultElement.value = percentage.toFixed(2);
                resultElement.dispatchEvent(new Event('input'));
            }
        }
    }

    /**
     * Calculate ratio
     */
    calculateRatio(input) {
        const value1 = parseFloat(input.value || '0');
        const value2Input = document.querySelector(input.dataset.value2);
        const value2 = parseFloat(value2Input?.value || '0');
        
        if (value2 > 0) {
            const ratio = value1 / value2;
            const resultElement = document.querySelector(input.dataset.result);
            if (resultElement) {
                resultElement.value = ratio.toFixed(2);
                resultElement.dispatchEvent(new Event('input'));
            }
        }
    }

    /**
     * Calculate average
     */
    calculateAverage(input) {
        const inputs = document.querySelectorAll(input.dataset.inputs);
        let sum = 0;
        let count = 0;
        
        inputs.forEach(inp => {
            const value = parseFloat(inp.value || '0');
            if (value > 0) {
                sum += value;
                count++;
            }
        });
        
        const average = count > 0 ? sum / count : 0;
        const resultElement = document.querySelector(input.dataset.result);
        if (resultElement) {
            resultElement.value = average.toFixed(2);
            resultElement.dispatchEvent(new Event('input'));
        }
    }

    /**
     * Calculate total
     */
    calculateTotal(input) {
        const inputs = document.querySelectorAll(input.dataset.inputs);
        let total = 0;
        
        inputs.forEach(inp => {
            const value = parseFloat(inp.value || '0');
            total += value;
        });
        
        const resultElement = document.querySelector(input.dataset.result);
        if (resultElement) {
            resultElement.value = total.toFixed(2);
            resultElement.dispatchEvent(new Event('input'));
        }
    }

    /**
     * Setup workflow handlers
     */
    setupWorkflowHandlers() {
        // Submit for review
        const submitButton = document.getElementById('submitAssessment');
        if (submitButton) {
            submitButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitAssessment();
            });
        }
        
        // Approve assessment
        const approveButton = document.getElementById('approveAssessment');
        if (approveButton) {
            approveButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.approveAssessment();
            });
        }
        
        // Reject assessment
        const rejectButton = document.getElementById('rejectAssessment');
        if (rejectButton) {
            rejectButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.rejectAssessment();
            });
        }
        
        // Request revision
        const reviseButton = document.getElementById('requestRevision');
        if (reviseButton) {
            reviseButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.requestRevision();
            });
        }
    }

    /**
     * Submit assessment for review
     */
    async submitAssessment() {
        if (!this.validateAssessment()) {
            this.showNotification('Mohon lengkapi semua penilaian sebelum submit', 'error');
            return;
        }
        
        const formData = this.collectAssessmentData();
        
        try {
            const response = await fetch('/sakip/assessments/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Penilaian berhasil disubmit untuk review', 'success');
                // Redirect or update UI
                setTimeout(() => {
                    window.location.href = result.redirect || '/sakip/assessments';
                }, 2000);
            } else {
                this.showNotification(result.message || 'Gagal submit penilaian', 'error');
            }
            
        } catch (error) {
            console.error('Submit error:', error);
            this.showNotification('Terjadi kesalahan saat submit penilaian', 'error');
        }
    }

    /**
     * Validate assessment before submission
     */
    validateAssessment() {
        const scoreInputs = document.querySelectorAll('.assessment-score');
        let isValid = true;
        
        scoreInputs.forEach(input => {
            if (!this.validateScore(input)) {
                isValid = false;
            }
        });
        
        // Check if all required fields are filled
        const requiredFields = document.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
        
        return isValid;
    }

    /**
     * Collect assessment data
     */
    collectAssessmentData() {
        const data = {
            scores: {},
            comments: {},
            recommendations: {},
            total_score: this.currentScore
        };
        
        // Collect scores
        const scoreInputs = document.querySelectorAll('.assessment-score');
        scoreInputs.forEach(input => {
            const criterionId = input.dataset.criterionId;
            data.scores[criterionId] = {
                score: parseFloat(input.value || '0'),
                max_score: parseFloat(input.dataset.maxScore || '0'),
                weight: parseFloat(input.dataset.weight || '0')
            };
        });
        
        // Collect comments
        const commentInputs = document.querySelectorAll('.assessment-comment');
        commentInputs.forEach(input => {
            const criterionId = input.dataset.criterionId;
            data.comments[criterionId] = input.value;
        });
        
        // Collect recommendations
        const recommendationInputs = document.querySelectorAll('.assessment-recommendation');
        recommendationInputs.forEach(input => {
            const criterionId = input.dataset.criterionId;
            data.recommendations[criterionId] = input.value;
        });
        
        return data;
    }

    /**
     * Initialize assessment forms
     */
    initializeAssessmentForms() {
        // Setup evidence preview
        const evidenceLinks = document.querySelectorAll('.evidence-link');
        evidenceLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.previewEvidence(link.href);
            });
        });
        
        // Setup performance data comparison
        const compareButtons = document.querySelectorAll('.compare-data-btn');
        compareButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.showDataComparison(button.dataset.indicatorId);
            });
        });
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Implementation similar to other modules
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

// Initialize assessment when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.sakipAssessment = new SakipAssessment();
});