/**
 * SAKIP Dynamic Forms
 * Government-style dynamic form handling and validation for SAKIP module
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
        global.SAKIP_DYNAMIC_FORMS = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Form Configuration Constants
     */
    const FORM_CONSTANTS = {
        // Field types
        FIELD_TYPES: {
            TEXT: 'text',
            TEXTAREA: 'textarea',
            NUMBER: 'number',
            EMAIL: 'email',
            PHONE: 'phone',
            DATE: 'date',
            DATETIME: 'datetime-local',
            SELECT: 'select',
            MULTI_SELECT: 'multi-select',
            RADIO: 'radio',
            CHECKBOX: 'checkbox',
            FILE: 'file',
            PASSWORD: 'password',
            URL: 'url',
            TIME: 'time',
            RANGE: 'range',
            COLOR: 'color',
            HIDDEN: 'hidden'
        },

        // Validation types
        VALIDATION_TYPES: {
            REQUIRED: 'required',
            EMAIL: 'email',
            PHONE: 'phone',
            NUMBER: 'number',
            MIN_LENGTH: 'minLength',
            MAX_LENGTH: 'maxLength',
            MIN: 'min',
            MAX: 'max',
            PATTERN: 'pattern',
            CUSTOM: 'custom'
        },

        // Form themes
        THEMES: {
            GOVERNMENT: 'government',
            MODERN: 'modern',
            COMPACT: 'compact'
        },

        // Error messages (Indonesian)
        ERROR_MESSAGES: {
            required: 'Field ini wajib diisi',
            email: 'Format email tidak valid',
            phone: 'Format nomor telepon tidak valid',
            number: 'Field ini harus berupa angka',
            minLength: 'Minimal {min} karakter',
            maxLength: 'Maksimal {max} karakter',
            min: 'Nilai minimal adalah {min}',
            max: 'Nilai maksimal adalah {max}',
            pattern: 'Format tidak sesuai',
            fileSize: 'Ukuran file terlalu besar',
            fileType: 'Tipe file tidak diizinkan'
        }
    };

    /**
     * Form Field Generator
     */
    class FormFieldGenerator {
        constructor() {
            this.fieldCounter = 0;
        }

        /**
         * Generate form field
         */
        generateField(fieldConfig, formId) {
            this.fieldCounter++;
            const fieldId = fieldConfig.id || `${formId}_field_${this.fieldCounter}`;
            const fieldName = fieldConfig.name || fieldId;

            const fieldContainer = document.createElement('div');
            fieldContainer.className = this.getFieldContainerClass(fieldConfig);
            fieldContainer.dataset.fieldName = fieldName;

            // Generate label
            if (fieldConfig.label !== false) {
                const label = this.generateLabel(fieldConfig, fieldId);
                fieldContainer.appendChild(label);
            }

            // Generate field based on type
            let fieldElement;
            switch (fieldConfig.type) {
                case FORM_CONSTANTS.FIELD_TYPES.TEXTAREA:
                    fieldElement = this.generateTextarea(fieldConfig, fieldId, fieldName);
                    break;
                case FORM_CONSTANTS.FIELD_TYPES.SELECT:
                    fieldElement = this.generateSelect(fieldConfig, fieldId, fieldName);
                    break;
                case FORM_CONSTANTS.FIELD_TYPES.MULTI_SELECT:
                    fieldElement = this.generateMultiSelect(fieldConfig, fieldId, fieldName);
                    break;
                case FORM_CONSTANTS.FIELD_TYPES.RADIO:
                    fieldElement = this.generateRadioGroup(fieldConfig, fieldId, fieldName);
                    break;
                case FORM_CONSTANTS.FIELD_TYPES.CHECKBOX:
                    fieldElement = this.generateCheckbox(fieldConfig, fieldId, fieldName);
                    break;
                case FORM_CONSTANTS.FIELD_TYPES.FILE:
                    fieldElement = this.generateFileInput(fieldConfig, fieldId, fieldName);
                    break;
                case FORM_CONSTANTS.FIELD_TYPES.RANGE:
                    fieldElement = this.generateRangeInput(fieldConfig, fieldId, fieldName);
                    break;
                default:
                    fieldElement = this.generateInput(fieldConfig, fieldId, fieldName);
            }

            if (fieldElement) {
                fieldContainer.appendChild(fieldElement);
            }

            // Generate help text
            if (fieldConfig.helpText) {
                const helpText = this.generateHelpText(fieldConfig.helpText);
                fieldContainer.appendChild(helpText);
            }

            // Generate error container
            const errorContainer = this.generateErrorContainer(fieldId);
            fieldContainer.appendChild(errorContainer);

            return fieldContainer;
        }

        /**
         * Generate label
         */
        generateLabel(fieldConfig, fieldId) {
            const label = document.createElement('label');
            label.className = 'sakip-form-label';
            label.htmlFor = fieldId;
            label.textContent = fieldConfig.label;

            if (fieldConfig.required) {
                const requiredSpan = document.createElement('span');
                requiredSpan.className = 'sakip-required-indicator';
                requiredSpan.textContent = ' *';
                label.appendChild(requiredSpan);
            }

            return label;
        }

        /**
         * Generate input element
         */
        generateInput(fieldConfig, fieldId, fieldName) {
            const input = document.createElement('input');
            input.type = fieldConfig.type || 'text';
            input.id = fieldId;
            input.name = fieldName;
            input.className = this.getInputClass(fieldConfig);
            
            if (fieldConfig.placeholder) {
                input.placeholder = fieldConfig.placeholder;
            }

            if (fieldConfig.value !== undefined) {
                input.value = fieldConfig.value;
            }

            if (fieldConfig.disabled) {
                input.disabled = true;
            }

            if (fieldConfig.readOnly) {
                input.readOnly = true;
            }

            if (fieldConfig.attributes) {
                Object.keys(fieldConfig.attributes).forEach(attr => {
                    input.setAttribute(attr, fieldConfig.attributes[attr]);
                });
            }

            return input;
        }

        /**
         * Generate textarea element
         */
        generateTextarea(fieldConfig, fieldId, fieldName) {
            const textarea = document.createElement('textarea');
            textarea.id = fieldId;
            textarea.name = fieldName;
            textarea.className = this.getInputClass(fieldConfig);
            textarea.rows = fieldConfig.rows || 4;

            if (fieldConfig.placeholder) {
                textarea.placeholder = fieldConfig.placeholder;
            }

            if (fieldConfig.value !== undefined) {
                textarea.value = fieldConfig.value;
            }

            if (fieldConfig.disabled) {
                textarea.disabled = true;
            }

            if (fieldConfig.readOnly) {
                textarea.readOnly = true;
            }

            return textarea;
        }

        /**
         * Generate select element
         */
        generateSelect(fieldConfig, fieldId, fieldName) {
            const select = document.createElement('select');
            select.id = fieldId;
            select.name = fieldName;
            select.className = this.getInputClass(fieldConfig);

            if (fieldConfig.placeholder) {
                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.textContent = fieldConfig.placeholder;
                placeholderOption.disabled = true;
                placeholderOption.selected = true;
                select.appendChild(placeholderOption);
            }

            if (fieldConfig.options) {
                fieldConfig.options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.label;
                    
                    if (fieldConfig.value === option.value) {
                        optionElement.selected = true;
                    }
                    
                    select.appendChild(optionElement);
                });
            }

            if (fieldConfig.disabled) {
                select.disabled = true;
            }

            return select;
        }

        /**
         * Generate multi-select element
         */
        generateMultiSelect(fieldConfig, fieldId, fieldName) {
            const container = document.createElement('div');
            container.className = 'sakip-multi-select-container';

            const select = document.createElement('select');
            select.id = fieldId;
            select.name = `${fieldName}[]`;
            select.className = this.getInputClass(fieldConfig);
            select.multiple = true;
            select.size = fieldConfig.size || 4;

            if (fieldConfig.options) {
                fieldConfig.options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.label;
                    
                    if (fieldConfig.value && fieldConfig.value.includes(option.value)) {
                        optionElement.selected = true;
                    }
                    
                    select.appendChild(optionElement);
                });
            }

            if (fieldConfig.disabled) {
                select.disabled = true;
            }

            container.appendChild(select);

            // Add selection controls
            const controls = this.generateMultiSelectControls(select, fieldConfig);
            container.appendChild(controls);

            return container;
        }

        /**
         * Generate radio group
         */
        generateRadioGroup(fieldConfig, fieldId, fieldName) {
            const container = document.createElement('div');
            container.className = 'sakip-radio-group';

            if (fieldConfig.options) {
                fieldConfig.options.forEach((option, index) => {
                    const radioContainer = document.createElement('div');
                    radioContainer.className = 'sakip-radio-item';

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.id = `${fieldId}_${index}`;
                    radio.name = fieldName;
                    radio.value = option.value;
                    radio.className = 'sakip-radio-input';

                    if (fieldConfig.value === option.value) {
                        radio.checked = true;
                    }

                    if (fieldConfig.disabled) {
                        radio.disabled = true;
                    }

                    const label = document.createElement('label');
                    label.htmlFor = `${fieldId}_${index}`;
                    label.className = 'sakip-radio-label';
                    label.textContent = option.label;

                    radioContainer.appendChild(radio);
                    radioContainer.appendChild(label);
                    container.appendChild(radioContainer);
                });
            }

            return container;
        }

        /**
         * Generate checkbox
         */
        generateCheckbox(fieldConfig, fieldId, fieldName) {
            const container = document.createElement('div');
            container.className = 'sakip-checkbox-container';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.id = fieldId;
            checkbox.name = fieldName;
            checkbox.className = 'sakip-checkbox-input';
            checkbox.value = fieldConfig.value || '1';

            if (fieldConfig.checked) {
                checkbox.checked = true;
            }

            if (fieldConfig.disabled) {
                checkbox.disabled = true;
            }

            const label = document.createElement('label');
            label.htmlFor = fieldId;
            label.className = 'sakip-checkbox-label';
            label.textContent = fieldConfig.checkboxLabel || fieldConfig.label;

            container.appendChild(checkbox);
            container.appendChild(label);

            return container;
        }

        /**
         * Generate file input
         */
        generateFileInput(fieldConfig, fieldId, fieldName) {
            const container = document.createElement('div');
            container.className = 'sakip-file-input-container';

            const input = document.createElement('input');
            input.type = 'file';
            input.id = fieldId;
            input.name = fieldName;
            input.className = this.getInputClass(fieldConfig);

            if (fieldConfig.multiple) {
                input.multiple = true;
            }

            if (fieldConfig.accept) {
                input.accept = fieldConfig.accept;
            }

            if (fieldConfig.disabled) {
                input.disabled = true;
            }

            // File preview container
            const previewContainer = document.createElement('div');
            previewContainer.className = 'sakip-file-preview';
            previewContainer.id = `${fieldId}_preview`;

            container.appendChild(input);
            container.appendChild(previewContainer);

            return container;
        }

        /**
         * Generate range input
         */
        generateRangeInput(fieldConfig, fieldId, fieldName) {
            const container = document.createElement('div');
            container.className = 'sakip-range-input-container';

            const input = document.createElement('input');
            input.type = 'range';
            input.id = fieldId;
            input.name = fieldName;
            input.className = this.getInputClass(fieldConfig);

            if (fieldConfig.min !== undefined) {
                input.min = fieldConfig.min;
            }

            if (fieldConfig.max !== undefined) {
                input.max = fieldConfig.max;
            }

            if (fieldConfig.step !== undefined) {
                input.step = fieldConfig.step;
            }

            if (fieldConfig.value !== undefined) {
                input.value = fieldConfig.value;
            }

            if (fieldConfig.disabled) {
                input.disabled = true;
            }

            // Range value display
            const valueDisplay = document.createElement('span');
            valueDisplay.className = 'sakip-range-value';
            valueDisplay.textContent = input.value;

            input.addEventListener('input', (e) => {
                valueDisplay.textContent = e.target.value;
            });

            container.appendChild(input);
            container.appendChild(valueDisplay);

            return container;
        }

        /**
         * Generate multi-select controls
         */
        generateMultiSelectControls(selectElement, fieldConfig) {
            const controls = document.createElement('div');
            controls.className = 'sakip-multi-select-controls';

            const selectAllBtn = document.createElement('button');
            selectAllBtn.type = 'button';
            selectAllBtn.className = 'sakip-btn sakip-btn-sm sakip-btn-secondary';
            selectAllBtn.textContent = 'Pilih Semua';
            selectAllBtn.addEventListener('click', () => {
                Array.from(selectElement.options).forEach(option => {
                    option.selected = true;
                });
            });

            const deselectAllBtn = document.createElement('button');
            deselectAllBtn.type = 'button';
            deselectAllBtn.className = 'sakip-btn sakip-btn-sm sakip-btn-secondary';
            deselectAllBtn.textContent = 'Hapus Semua';
            deselectAllBtn.addEventListener('click', () => {
                Array.from(selectElement.options).forEach(option => {
                    option.selected = false;
                });
            });

            controls.appendChild(selectAllBtn);
            controls.appendChild(deselectAllBtn);

            return controls;
        }

        /**
         * Generate help text
         */
        generateHelpText(helpText) {
            const helpElement = document.createElement('small');
            helpElement.className = 'sakip-form-help';
            helpElement.textContent = helpText;
            return helpElement;
        }

        /**
         * Generate error container
         */
        generateErrorContainer(fieldId) {
            const errorContainer = document.createElement('div');
            errorContainer.className = 'sakip-form-error';
            errorContainer.id = `${fieldId}_error`;
            errorContainer.style.display = 'none';
            return errorContainer;
        }

        /**
         * Get field container class
         */
        getFieldContainerClass(fieldConfig) {
            const classes = ['sakip-form-group'];
            
            if (fieldConfig.grid) {
                classes.push(`sakip-col-${fieldConfig.grid}`);
            }
            
            if (fieldConfig.className) {
                classes.push(fieldConfig.className);
            }

            return classes.join(' ');
        }

        /**
         * Get input class
         */
        getInputClass(fieldConfig) {
            const classes = ['sakip-form-control'];
            
            if (fieldConfig.size) {
                classes.push(`sakip-form-control-${fieldConfig.size}`);
            }

            return classes.join(' ');
        }
    }

    /**
     * Form Validator
     */
    class FormValidator {
        constructor() {
            this.validators = new Map();
            this.customValidators = new Map();
            this.setupDefaultValidators();
        }

        /**
         * Setup default validators
         */
        setupDefaultValidators() {
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.REQUIRED, this.validateRequired.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.EMAIL, this.validateEmail.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.PHONE, this.validatePhone.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.NUMBER, this.validateNumber.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.MIN_LENGTH, this.validateMinLength.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.MAX_LENGTH, this.validateMaxLength.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.MIN, this.validateMin.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.MAX, this.validateMax.bind(this));
            this.validators.set(FORM_CONSTANTS.VALIDATION_TYPES.PATTERN, this.validatePattern.bind(this));
        }

        /**
         * Validate field
         */
        validateField(fieldElement, validationRules) {
            const errors = [];

            validationRules.forEach(rule => {
                const validator = this.validators.get(rule.type);
                if (validator) {
                    const result = validator(fieldElement, rule);
                    if (!result.valid) {
                        errors.push(result.message);
                    }
                }
            });

            return {
                valid: errors.length === 0,
                errors: errors
            };
        }

        /**
         * Validate required field
         */
        validateRequired(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            const isValid = value !== null && value !== undefined && value !== '';

            return {
                valid: isValid,
                message: isValid ? '' : FORM_CONSTANTS.ERROR_MESSAGES.required
            };
        }

        /**
         * Validate email
         */
        validateEmail(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const isValid = emailRegex.test(value);

            return {
                valid: isValid,
                message: isValid ? '' : FORM_CONSTANTS.ERROR_MESSAGES.email
            };
        }

        /**
         * Validate phone number
         */
        validatePhone(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            // Indonesian phone number validation
            const phoneRegex = /^[+]?[0-9]{10,15}$/;
            const isValid = phoneRegex.test(value.replace(/\s|-/g, ''));

            return {
                valid: isValid,
                message: isValid ? '' : FORM_CONSTANTS.ERROR_MESSAGES.phone
            };
        }

        /**
         * Validate number
         */
        validateNumber(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            const isValid = !isNaN(value) && !isNaN(parseFloat(value));

            return {
                valid: isValid,
                message: isValid ? '' : FORM_CONSTANTS.ERROR_MESSAGES.number
            };
        }

        /**
         * Validate minimum length
         */
        validateMinLength(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            const isValid = value.length >= rule.min;
            const message = FORM_CONSTANTS.ERROR_MESSAGES.minLength.replace('{min}', rule.min);

            return {
                valid: isValid,
                message: isValid ? '' : message
            };
        }

        /**
         * Validate maximum length
         */
        validateMaxLength(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            const isValid = value.length <= rule.max;
            const message = FORM_CONSTANTS.ERROR_MESSAGES.maxLength.replace('{max}', rule.max);

            return {
                valid: isValid,
                message: isValid ? '' : message
            };
        }

        /**
         * Validate minimum value
         */
        validateMin(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            const numValue = parseFloat(value);
            const isValid = !isNaN(numValue) && numValue >= rule.min;
            const message = FORM_CONSTANTS.ERROR_MESSAGES.min.replace('{min}', rule.min);

            return {
                valid: isValid,
                message: isValid ? '' : message
            };
        }

        /**
         * Validate maximum value
         */
        validateMax(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            const numValue = parseFloat(value);
            const isValid = !isNaN(numValue) && numValue <= rule.max;
            const message = FORM_CONSTANTS.ERROR_MESSAGES.max.replace('{max}', rule.max);

            return {
                valid: isValid,
                message: isValid ? '' : message
            };
        }

        /**
         * Validate pattern
         */
        validatePattern(fieldElement, rule) {
            const value = this.getFieldValue(fieldElement);
            if (!value) return { valid: true, message: '' };

            const regex = new RegExp(rule.pattern);
            const isValid = regex.test(value);

            return {
                valid: isValid,
                message: isValid ? '' : rule.message || FORM_CONSTANTS.ERROR_MESSAGES.pattern
            };
        }

        /**
         * Get field value
         */
        getFieldValue(fieldElement) {
            if (fieldElement.type === 'checkbox') {
                return fieldElement.checked;
            }
            
            if (fieldElement.type === 'radio') {
                const radioGroup = document.querySelectorAll(`input[name="${fieldElement.name}"]`);
                const checkedRadio = Array.from(radioGroup).find(radio => radio.checked);
                return checkedRadio ? checkedRadio.value : null;
            }
            
            if (fieldElement.tagName === 'SELECT' && fieldElement.multiple) {
                return Array.from(fieldElement.selectedOptions).map(option => option.value);
            }

            return fieldElement.value;
        }

        /**
         * Add custom validator
         */
        addCustomValidator(name, validatorFunction) {
            this.customValidators.set(name, validatorFunction);
        }

        /**
         * Validate form
         */
        validateForm(formElement) {
            const fields = formElement.querySelectorAll('[data-validation]');
            const errors = new Map();

            fields.forEach(field => {
                const validationRules = JSON.parse(field.dataset.validation || '[]');
                const result = this.validateField(field, validationRules);
                
                if (!result.valid) {
                    errors.set(field.name, result.errors);
                }
            });

            return {
                valid: errors.size === 0,
                errors: Object.fromEntries(errors)
            };
        }
    }

    /**
     * Form Manager
     */
    class FormManager {
        constructor() {
            this.forms = new Map();
            this.fieldGenerator = new FormFieldGenerator();
            this.validator = new FormValidator();
            this.formSubmissions = new Map();
        }

        /**
         * Create dynamic form
         */
        createForm(formConfig) {
            const formId = formConfig.id || `form_${Date.now()}`;
            const form = document.createElement('form');
            form.id = formId;
            form.className = this.getFormClass(formConfig);
            form.dataset.formId = formId;

            // Add form header
            if (formConfig.title) {
                const header = this.generateFormHeader(formConfig);
                form.appendChild(header);
            }

            // Add form fields
            if (formConfig.fields) {
                const fieldsContainer = this.generateFieldsContainer(formConfig);
                
                formConfig.fields.forEach(fieldConfig => {
                    const fieldElement = this.fieldGenerator.generateField(fieldConfig, formId);
                    fieldsContainer.appendChild(fieldElement);
                });

                form.appendChild(fieldsContainer);
            }

            // Add form actions
            if (formConfig.actions) {
                const actionsContainer = this.generateFormActions(formConfig);
                form.appendChild(actionsContainer);
            }

            // Add form footer
            if (formConfig.footer) {
                const footer = this.generateFormFooter(formConfig);
                form.appendChild(footer);
            }

            // Setup form event handlers
            this.setupFormEventHandlers(form, formConfig);

            // Store form reference
            this.forms.set(formId, {
                element: form,
                config: formConfig,
                validationRules: this.extractValidationRules(formConfig.fields)
            });

            return form;
        }

        /**
         * Generate form header
         */
        generateFormHeader(formConfig) {
            const header = document.createElement('div');
            header.className = 'sakip-form-header';

            const title = document.createElement('h3');
            title.className = 'sakip-form-title';
            title.textContent = formConfig.title;

            header.appendChild(title);

            if (formConfig.description) {
                const description = document.createElement('p');
                description.className = 'sakip-form-description';
                description.textContent = formConfig.description;
                header.appendChild(description);
            }

            return header;
        }

        /**
         * Generate fields container
         */
        generateFieldsContainer(formConfig) {
            const container = document.createElement('div');
            container.className = 'sakip-form-fields';

            if (formConfig.layout === 'grid') {
                container.classList.add('sakip-form-grid');
            }

            return container;
        }

        /**
         * Generate form actions
         */
        generateFormActions(formConfig) {
            const actionsContainer = document.createElement('div');
            actionsContainer.className = 'sakip-form-actions';

            formConfig.actions.forEach(actionConfig => {
                const button = this.generateActionButton(actionConfig);
                actionsContainer.appendChild(button);
            });

            return actionsContainer;
        }

        /**
         * Generate action button
         */
        generateActionButton(actionConfig) {
            const button = document.createElement('button');
            button.type = actionConfig.type || 'button';
            button.className = this.getButtonClass(actionConfig);
            button.textContent = actionConfig.label;
            button.dataset.action = actionConfig.action;

            if (actionConfig.disabled) {
                button.disabled = true;
            }

            return button;
        }

        /**
         * Generate form footer
         */
        generateFormFooter(formConfig) {
            const footer = document.createElement('div');
            footer.className = 'sakip-form-footer';

            if (typeof formConfig.footer === 'string') {
                footer.textContent = formConfig.footer;
            } else if (formConfig.footer.content) {
                footer.innerHTML = formConfig.footer.content;
            }

            return footer;
        }

        /**
         * Setup form event handlers
         */
        setupFormEventHandlers(form, formConfig) {
            // Form submission
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form, formConfig);
            });

            // Action button clicks
            form.addEventListener('click', (e) => {
                if (e.target.dataset.action) {
                    this.handleActionClick(e, form, formConfig);
                }
            });

            // Field validation on blur
            form.addEventListener('blur', (e) => {
                if (e.target.dataset.validation) {
                    this.validateField(e.target);
                }
            }, true);

            // Real-time validation
            form.addEventListener('input', (e) => {
                if (e.target.dataset.validation && formConfig.realTimeValidation) {
                    this.validateField(e.target);
                }
            });

            // File input handling
            form.addEventListener('change', (e) => {
                if (e.target.type === 'file') {
                    this.handleFileInputChange(e.target);
                }
            });
        }

        /**
         * Handle form submission
         */
        async handleFormSubmit(form, formConfig) {
            const formId = form.id;
            const submitButton = form.querySelector('[type="submit"]');
            
            // Validate form
            const validationResult = this.validator.validateForm(form);
            
            if (!validationResult.valid) {
                this.displayValidationErrors(form, validationResult.errors);
                return;
            }

            // Disable submit button
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Memproses...';
            }

            try {
                // Get form data
                const formData = this.getFormData(form);

                // Call submit handler
                if (formConfig.onSubmit) {
                    await formConfig.onSubmit(formData, form);
                } else {
                    // Default form submission
                    await this.defaultFormSubmit(form, formData);
                }

                // Clear validation errors
                this.clearValidationErrors(form);

                // Show success message
                if (formConfig.showSuccess !== false) {
                    this.showSuccessMessage(form, formConfig.successMessage || 'Form berhasil disubmit');
                }

            } catch (error) {
                console.error('Form submission error:', error);
                this.showErrorMessage(form, error.message || 'Terjadi kesalahan saat submit form');
            } finally {
                // Re-enable submit button
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = submitButton.dataset.originalText || 'Submit';
                }
            }
        }

        /**
         * Handle action button click
         */
        handleActionClick(event, form, formConfig) {
            const action = event.target.dataset.action;
            
            if (action === 'reset') {
                form.reset();
                this.clearValidationErrors(form);
            } else if (action === 'cancel') {
                if (formConfig.onCancel) {
                    formConfig.onCancel(form);
                } else {
                    form.reset();
                }
            } else if (formConfig.onAction) {
                formConfig.onAction(action, form);
            }
        }

        /**
         * Validate field
         */
        validateField(fieldElement) {
            const validationRules = JSON.parse(fieldElement.dataset.validation || '[]');
            const result = this.validator.validateField(fieldElement, validationRules);

            this.displayFieldValidationResult(fieldElement, result);
            return result;
        }

        /**
         * Display field validation result
         */
        displayFieldValidationResult(fieldElement, result) {
            const errorContainer = document.getElementById(`${fieldElement.id}_error`);
            
            if (result.valid) {
                fieldElement.classList.remove('is-invalid');
                fieldElement.classList.add('is-valid');
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                    errorContainer.textContent = '';
                }
            } else {
                fieldElement.classList.remove('is-valid');
                fieldElement.classList.add('is-invalid');
                if (errorContainer) {
                    errorContainer.style.display = 'block';
                    errorContainer.textContent = result.errors.join(', ');
                }
            }
        }

        /**
         * Display validation errors
         */
        displayValidationErrors(form, errors) {
            // Clear previous errors
            this.clearValidationErrors(form);

            // Display new errors
            Object.keys(errors).forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    const result = { valid: false, errors: errors[fieldName] };
                    this.displayFieldValidationResult(field, result);
                }
            });

            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        /**
         * Clear validation errors
         */
        clearValidationErrors(form) {
            const fields = form.querySelectorAll('.is-invalid, .is-valid');
            fields.forEach(field => {
                field.classList.remove('is-invalid', 'is-valid');
                const errorContainer = document.getElementById(`${field.id}_error`);
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                    errorContainer.textContent = '';
                }
            });
        }

        /**
         * Get form data
         */
        getFormData(form) {
            const formData = new FormData(form);
            const data = {};

            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    // Handle multiple values (checkboxes, multi-select)
                    if (!Array.isArray(data[key])) {
                        data[key] = [data[key]];
                    }
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            }

            // Handle checkboxes
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                data[checkbox.name] = checkbox.checked;
            });

            return data;
        }

        /**
         * Handle file input change
         */
        handleFileInputChange(fileInput) {
            const previewContainer = document.getElementById(`${fileInput.id}_preview`);
            if (!previewContainer) return;

            previewContainer.innerHTML = '';

            if (fileInput.files && fileInput.files.length > 0) {
                Array.from(fileInput.files).forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'sakip-file-item';
                    fileItem.textContent = `${file.name} (${this.formatFileSize(file.size)})`;
                    previewContainer.appendChild(fileItem);
                });
            }
        }

        /**
         * Format file size
         */
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        /**
         * Show success message
         */
        showSuccessMessage(form, message) {
            const alert = document.createElement('div');
            alert.className = 'sakip-alert sakip-alert-success';
            alert.textContent = message;
            
            form.insertBefore(alert, form.firstChild);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        /**
         * Show error message
         */
        showErrorMessage(form, message) {
            const alert = document.createElement('div');
            alert.className = 'sakip-alert sakip-alert-error';
            alert.textContent = message;
            
            form.insertBefore(alert, form.firstChild);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        /**
         * Default form submission
         */
        async defaultFormSubmit(form, formData) {
            const action = form.action;
            const method = form.method || 'POST';

            const response = await fetch(action, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        }

        /**
         * Extract validation rules
         */
        extractValidationRules(fields) {
            const rules = {};
            
            fields.forEach(field => {
                if (field.validation) {
                    rules[field.name] = field.validation;
                }
            });

            return rules;
        }

        /**
         * Get form class
         */
        getFormClass(formConfig) {
            const classes = ['sakip-form'];
            
            if (formConfig.theme) {
                classes.push(`sakip-form-${formConfig.theme}`);
            }
            
            if (formConfig.className) {
                classes.push(formConfig.className);
            }

            return classes.join(' ');
        }

        /**
         * Get button class
         */
        getButtonClass(actionConfig) {
            const classes = ['sakip-btn'];
            
            if (actionConfig.variant) {
                classes.push(`sakip-btn-${actionConfig.variant}`);
            }
            
            if (actionConfig.size) {
                classes.push(`sakip-btn-${actionConfig.size}`);
            }

            return classes.join(' ');
        }

        /**
         * Get form data by ID
         */
        getFormDataById(formId) {
            const formData = this.forms.get(formId);
            if (!formData) {
                throw new Error(`Form with ID '${formId}' not found`);
            }

            return this.getFormData(formData.element);
        }

        /**
         * Validate form by ID
         */
        validateFormById(formId) {
            const formData = this.forms.get(formId);
            if (!formData) {
                throw new Error(`Form with ID '${formId}' not found`);
            }

            return this.validator.validateForm(formData.element);
        }

        /**
         * Reset form by ID
         */
        resetFormById(formId) {
            const formData = this.forms.get(formId);
            if (!formData) {
                throw new Error(`Form with ID '${formId}' not found`);
            }

            formData.element.reset();
            this.clearValidationErrors(formData.element);
        }

        /**
         * Destroy form by ID
         */
        destroyFormById(formId) {
            const formData = this.forms.get(formId);
            if (formData) {
                formData.element.remove();
                this.forms.delete(formId);
            }
        }
    }

    /**
     * Form Utilities
     */
    class FormUtilities {
        /**
         * Serialize form data
         */
        static serializeForm(formElement) {
            const formData = new FormData(formElement);
            const serialized = {};

            for (let [key, value] of formData.entries()) {
                if (serialized[key]) {
                    if (!Array.isArray(serialized[key])) {
                        serialized[key] = [serialized[key]];
                    }
                    serialized[key].push(value);
                } else {
                    serialized[key] = value;
                }
            }

            return serialized;
        }

        /**
         * Populate form with data
         */
        static populateForm(formElement, data) {
            Object.keys(data).forEach(key => {
                const field = formElement.querySelector(`[name="${key}"]`);
                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = Boolean(data[key]);
                    } else if (field.type === 'radio') {
                        const radio = formElement.querySelector(`input[name="${key}"][value="${data[key]}"]`);
                        if (radio) {
                            radio.checked = true;
                        }
                    } else {
                        field.value = data[key];
                    }
                }
            });
        }

        /**
         * Clear form
         */
        static clearForm(formElement) {
            formElement.reset();
            
            // Clear custom fields
            const customFields = formElement.querySelectorAll('.sakip-file-preview, .sakip-range-value');
            customFields.forEach(field => {
                field.innerHTML = '';
            });
        }

        /**
         * Enable form
         */
        static enableForm(formElement) {
            const fields = formElement.querySelectorAll('input, select, textarea, button');
            fields.forEach(field => {
                field.disabled = false;
            });
        }

        /**
         * Disable form
         */
        static disableForm(formElement) {
            const fields = formElement.querySelectorAll('input, select, textarea, button');
            fields.forEach(field => {
                field.disabled = true;
            });
        }

        /**
         * Show loading state
         */
        static showLoading(formElement, message = 'Processing...') {
            const submitButton = formElement.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.dataset.originalText = submitButton.textContent;
                submitButton.textContent = message;
                submitButton.disabled = true;
            }
        }

        /**
         * Hide loading state
         */
        static hideLoading(formElement) {
            const submitButton = formElement.querySelector('[type="submit"]');
            if (submitButton && submitButton.dataset.originalText) {
                submitButton.textContent = submitButton.dataset.originalText;
                submitButton.disabled = false;
                delete submitButton.dataset.originalText;
            }
        }
    }

    /**
     * Main SAKIP Dynamic Forms API
     */
    const SAKIP_DYNAMIC_FORMS = {
        // Constants
        constants: FORM_CONSTANTS,

        // Core classes
        FormFieldGenerator,
        FormValidator,
        FormManager,
        FormUtilities,

        // Create instances
        fieldGenerator: new FormFieldGenerator(),
        validator: new FormValidator(),
        manager: new FormManager(),

        // Convenience methods
        createForm: (formConfig) => manager.createForm(formConfig),
        
        // Form management methods
        getFormData: (formId) => manager.getFormDataById(formId),
        validateForm: (formId) => manager.validateFormById(formId),
        resetForm: (formId) => manager.resetFormById(formId),
        destroyForm: (formId) => manager.destroyFormById(formId),

        // Validation methods
        validateField: (fieldElement, validationRules) => validator.validateField(fieldElement, validationRules),
        addCustomValidator: (name, validatorFunction) => validator.addCustomValidator(name, validatorFunction),

        // Utility methods
        serializeForm: (formElement) => FormUtilities.serializeForm(formElement),
        populateForm: (formElement, data) => FormUtilities.populateForm(formElement, data),
        clearForm: (formElement) => FormUtilities.clearForm(formElement),
        enableForm: (formElement) => FormUtilities.enableForm(formElement),
        disableForm: (formElement) => FormUtilities.disableForm(formElement),
        showLoading: (formElement, message) => FormUtilities.showLoading(formElement, message),
        hideLoading: (formElement) => FormUtilities.hideLoading(formElement),

        // Field generation methods
        generateField: (fieldConfig, formId) => fieldGenerator.generateField(fieldConfig, formId),

        // Validation types
        validationTypes: FORM_CONSTANTS.VALIDATION_TYPES,
        fieldTypes: FORM_CONSTANTS.FIELD_TYPES
    };

    return SAKIP_DYNAMIC_FORMS;
}));