/**
 * SAKIP File Upload with Progress and Validation
 * Handles file uploads with progress tracking, validation, and government-style design
 */

class SakipFileUpload {
    constructor() {
        this.uploads = new Map();
        this.activeUploads = 0;
        this.maxConcurrentUploads = 3;
        this.uploadQueue = [];
        this.isProcessingQueue = false;

        // Default configuration
        this.config = {
            maxFileSize: 10 * 1024 * 1024, // 10MB
            allowedTypes: [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/csv',
                'text/plain'
            ],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'doc', 'docx', 'csv', 'txt'],
            chunkSize: 2 * 1024 * 1024, // 2MB chunks for large files
            enableChunking: true,
            enableDragDrop: true,
            enablePreview: true,
            enableValidation: true,
            autoUpload: false,
            parallelUploads: true,
            retryAttempts: 3,
            retryDelay: 1000,
            uploadEndpoint: '/api/sakip/upload',
            validationEndpoint: '/api/sakip/validate-file'
        };

        // Indonesian text mappings
        this.texts = {
            selectFiles: 'Pilih File',
            dragDropHere: 'Seret dan lepas file di sini',
            or: 'atau',
            browse: 'Jelajahi',
            uploadComplete: 'Unggah selesai',
            uploadFailed: 'Unggah gagal',
            uploadCancelled: 'Unggah dibatalkan',
            uploading: 'Mengunggah',
            paused: 'Dijeda',
            retry: 'Coba lagi',
            cancel: 'Batal',
            remove: 'Hapus',
            preview: 'Pratinjau',
            download: 'Unduh',
            fileTooLarge: 'File terlalu besar',
            invalidFileType: 'Tipe file tidak valid',
            maxFilesExceeded: 'Jumlah file melebihi batas',
            networkError: 'Kesalahan jaringan',
            serverError: 'Kesalahan server',
            validationFailed: 'Validasi gagal',
            processing: 'Memproses',
            validating: 'Memvalidasi',
            fileInfo: 'Info File',
            fileSize: 'Ukuran',
            fileType: 'Tipe',
            lastModified: 'Terakhir diubah',
            uploadProgress: 'Progres unggah',
            uploadSpeed: 'Kecepatan',
            timeRemaining: 'Sisa waktu',
            chunksUploaded: 'Bagian terunggah',
            cancelUpload: 'Batalkan unggah',
            pauseUpload: 'Jeda unggah',
            resumeUpload: 'Lanjutkan unggah',
            removeFile: 'Hapus file',
            confirmRemove: 'Yakin ingin menghapus file ini?',
            uploadQueue: 'Antrian unggah',
            concurrentUploads: 'Unggah bersamaan',
            totalProgress: 'Progres total',
            filesSelected: 'file dipilih',
            clearAll: 'Bersihkan semua',
            startUpload: 'Mulai unggah',
            pauseAll: 'Jeda semua',
            resumeAll: 'Lanjutkan semua',
            cancelAll: 'Batalkan semua',
            fileValidation: 'Validasi file',
            checkingFile: 'Memeriksa file...',
            fileSafe: 'File aman',
            fileInfected: 'File terinfeksi',
            scanFailed: 'Pemindaian gagal'
        };

        // File type icons
        this.fileIcons = {
            image: 'fa-file-image',
            pdf: 'fa-file-pdf',
            excel: 'fa-file-excel',
            word: 'fa-file-word',
            csv: 'fa-file-csv',
            text: 'fa-file-alt',
            default: 'fa-file'
        };

        // Upload states
        this.uploadStates = {
            QUEUED: 'queued',
            VALIDATING: 'validating',
            UPLOADING: 'uploading',
            PAUSED: 'paused',
            COMPLETED: 'completed',
            FAILED: 'failed',
            CANCELLED: 'cancelled'
        };
    }

    /**
     * Initialize file upload
     */
    init(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error('File upload container not found');
            return;
        }

        // Merge configuration
        this.config = { ...this.config, ...options };
        
        this.createStructure();
        this.bindEvents();
        this.setupDragDrop();
        
        console.log('SAKIP File Upload initialized');
    }

    /**
     * Create upload structure
     */
    createStructure() {
        this.container.innerHTML = `
            <div class="sakip-file-upload">
                <!-- Upload Area -->
                <div class="upload-area">
                    <div class="upload-zone" id="upload-zone">
                        <div class="upload-zone-content">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                <p class="upload-main-text">${this.texts.dragDropHere}</p>
                                <p class="upload-sub-text">${this.texts.or} <span class="upload-browse">${this.texts.browse}</span></p>
                            </div>
                            <div class="upload-requirements">
                                <small>Max ${this.formatFileSize(this.config.maxFileSize)} • ${this.config.allowedExtensions.join(', ')}</small>
                            </div>
                        </div>
                        <input type="file" id="file-input" multiple accept="${this.config.allowedTypes.join(',')}" style="display: none;">
                    </div>
                </div>

                <!-- File List -->
                <div class="file-list" id="file-list">
                    <div class="file-list-header">
                        <div class="file-info">
                            <span class="file-count">0 ${this.texts.filesSelected}</span>
                        </div>
                        <div class="file-actions">
                            <button type="button" class="btn-start-all" disabled>
                                <i class="fas fa-play"></i> ${this.texts.startUpload}
                            </button>
                            <button type="button" class="btn-pause-all" disabled>
                                <i class="fas fa-pause"></i> ${this.texts.pauseAll}
                            </button>
                            <button type="button" class="btn-cancel-all" disabled>
                                <i class="fas fa-times"></i> ${this.texts.cancelAll}
                            </button>
                            <button type="button" class="btn-clear-all" disabled>
                                <i class="fas fa-trash"></i> ${this.texts.clearAll}
                            </button>
                        </div>
                    </div>
                    <div class="file-list-content">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- Upload Progress -->
                <div class="upload-progress" id="upload-progress" style="display: none;">
                    <div class="progress-header">
                        <h4>${this.texts.uploadProgress}</h4>
                        <div class="progress-stats">
                            <span class="uploaded-files">0</span> / <span class="total-files">0</span>
                            <span class="progress-percentage">0%</span>
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="progress-details">
                        <span class="upload-speed">0 KB/s</span>
                        <span class="time-remaining">--:--</span>
                    </div>
                </div>

                <!-- Validation Modal -->
                <div class="validation-modal" id="validation-modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4>${this.texts.fileValidation}</h4>
                            <button type="button" class="modal-close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="validation-content">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-cancel-validation">${this.texts.cancel}</button>
                            <button type="button" class="btn-continue-upload">${this.texts.startUpload}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        if (!this.container) return;

        // File input change
        const fileInput = this.container.querySelector('#file-input');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                this.handleFileSelect(e.target.files);
            });
        }

        // Browse button
        const browseButton = this.container.querySelector('.upload-browse');
        if (browseButton) {
            browseButton.addEventListener('click', () => {
                fileInput.click();
            });
        }

        // Bulk actions
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.btn-start-all')) {
                this.startAllUploads();
            } else if (e.target.closest('.btn-pause-all')) {
                this.pauseAllUploads();
            } else if (e.target.closest('.btn-cancel-all')) {
                this.cancelAllUploads();
            } else if (e.target.closest('.btn-clear-all')) {
                this.clearAllFiles();
            }
        });

        // Validation modal
        this.container.addEventListener('click', (e) => {
            if (e.target.closest('.modal-close') || e.target.closest('.btn-cancel-validation')) {
                this.closeValidationModal();
            } else if (e.target.closest('.btn-continue-upload')) {
                this.continueUpload();
            }
        });

        // Individual file actions
        this.container.addEventListener('click', (e) => {
            const fileItem = e.target.closest('.file-item');
            if (!fileItem) return;

            const fileId = fileItem.dataset.fileId;
            
            if (e.target.closest('.btn-start-upload')) {
                this.startUpload(fileId);
            } else if (e.target.closest('.btn-pause-upload')) {
                this.pauseUpload(fileId);
            } else if (e.target.closest('.btn-cancel-upload')) {
                this.cancelUpload(fileId);
            } else if (e.target.closest('.btn-remove-file')) {
                this.removeFile(fileId);
            } else if (e.target.closest('.btn-preview-file')) {
                this.previewFile(fileId);
            }
        });
    }

    /**
     * Setup drag and drop
     */
    setupDragDrop() {
        if (!this.config.enableDragDrop) return;

        const uploadZone = this.container.querySelector('#upload-zone');
        if (!uploadZone) return;

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        // Highlight drop zone
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.remove('drag-over');
            });
        });

        // Handle drop
        uploadZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFileSelect(files);
        });
    }

    /**
     * Handle file selection
     */
    handleFileSelect(files) {
        const fileArray = Array.from(files);
        
        // Validate files
        const validationResults = fileArray.map(file => this.validateFile(file));
        const validFiles = validationResults.filter(result => result.valid);
        const invalidFiles = validationResults.filter(result => !result.valid);

        // Show validation results
        if (invalidFiles.length > 0) {
            this.showValidationResults(validationResults);
        }

        // Add valid files
        validFiles.forEach(result => {
            this.addFile(result.file);
        });

        // Update UI
        this.updateFileList();
        this.updateBulkActions();
    }

    /**
     * Validate file
     */
    validateFile(file) {
        const result = {
            file: file,
            valid: true,
            errors: []
        };

        // Check file size
        if (file.size > this.config.maxFileSize) {
            result.valid = false;
            result.errors.push(this.texts.fileTooLarge);
        }

        // Check file type
        const extension = file.name.split('.').pop().toLowerCase();
        if (!this.config.allowedExtensions.includes(extension)) {
            result.valid = false;
            result.errors.push(this.texts.invalidFileType);
        }

        // Check MIME type
        if (!this.config.allowedTypes.includes(file.type)) {
            result.valid = false;
            result.errors.push(this.texts.invalidFileType);
        }

        return result;
    }

    /**
     * Add file to uploads
     */
    addFile(file) {
        const fileId = this.generateFileId(file);
        
        const upload = {
            id: fileId,
            file: file,
            name: file.name,
            size: file.size,
            type: file.type,
            extension: file.name.split('.').pop().toLowerCase(),
            state: this.uploadStates.QUEUED,
            progress: 0,
            uploadedBytes: 0,
            chunks: [],
            currentChunk: 0,
            retryCount: 0,
            startTime: null,
            endTime: null,
            speed: 0,
            timeRemaining: null,
            error: null,
            response: null
        };

        // Create chunks for large files
        if (this.config.enableChunking && file.size > this.config.chunkSize) {
            upload.chunks = this.createChunks(file);
        }

        this.uploads.set(fileId, upload);
        
        // Auto-upload if enabled
        if (this.config.autoUpload) {
            this.startUpload(fileId);
        }

        return fileId;
    }

    /**
     * Create file chunks
     */
    createChunks(file) {
        const chunks = [];
        const totalChunks = Math.ceil(file.size / this.config.chunkSize);
        
        for (let i = 0; i < totalChunks; i++) {
            const start = i * this.config.chunkSize;
            const end = Math.min(start + this.config.chunkSize, file.size);
            
            chunks.push({
                index: i,
                start: start,
                end: end,
                size: end - start,
                uploaded: false,
                retryCount: 0
            });
        }
        
        return chunks;
    }

    /**
     * Start upload
     */
    async startUpload(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        // Check concurrent uploads limit
        if (this.activeUploads >= this.maxConcurrentUploads) {
            this.uploadQueue.push(fileId);
            return;
        }

        upload.state = this.uploadStates.UPLOADING;
        upload.startTime = new Date();
        this.activeUploads++;

        this.updateFileItem(fileId);

        try {
            // Validate file on server if endpoint provided
            if (this.config.validationEndpoint) {
                upload.state = this.uploadStates.VALIDATING;
                this.updateFileItem(fileId);
                
                const validationResult = await this.validateFileOnServer(upload.file);
                if (!validationResult.valid) {
                    throw new Error(validationResult.error || this.texts.validationFailed);
                }
            }

            // Upload file
            if (upload.chunks.length > 0) {
                await this.uploadChunks(fileId);
            } else {
                await this.uploadSingleFile(fileId);
            }

            upload.state = this.uploadStates.COMPLETED;
            upload.endTime = new Date();
            
        } catch (error) {
            upload.state = this.uploadStates.FAILED;
            upload.error = error.message;
            
            // Retry if possible
            if (upload.retryCount < this.config.retryAttempts) {
                upload.retryCount++;
                setTimeout(() => {
                    this.startUpload(fileId);
                }, this.config.retryDelay);
                return;
            }
        } finally {
            this.activeUploads--;
            this.updateFileItem(fileId);
            this.processUploadQueue();
            this.updateOverallProgress();
        }
    }

    /**
     * Upload single file
     */
    async uploadSingleFile(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        const formData = new FormData();
        formData.append('file', upload.file);
        formData.append('name', upload.name);
        formData.append('size', upload.size);
        formData.append('type', upload.type);

        const xhr = new XMLHttpRequest();
        upload.xhr = xhr;

        return new Promise((resolve, reject) => {
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    upload.progress = (e.loaded / e.total) * 100;
                    upload.uploadedBytes = e.loaded;
                    upload.speed = this.calculateSpeed(upload.uploadedBytes, upload.startTime);
                    upload.timeRemaining = this.calculateTimeRemaining(upload.uploadedBytes, upload.size, upload.speed);
                    this.updateFileItem(fileId);
                    this.updateOverallProgress();
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        upload.response = response;
                        resolve(response);
                    } catch (error) {
                        reject(new Error('Invalid server response'));
                    }
                } else {
                    reject(new Error(`Upload failed: ${xhr.statusText}`));
                }
            });

            xhr.addEventListener('error', () => {
                reject(new Error('Network error'));
            });

            xhr.addEventListener('abort', () => {
                reject(new Error('Upload cancelled'));
            });

            xhr.open('POST', this.config.uploadEndpoint);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content);
            xhr.send(formData);
        });
    }

    /**
     * Upload chunks
     */
    async uploadChunks(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        for (let i = 0; i < upload.chunks.length; i++) {
            const chunk = upload.chunks[i];
            if (chunk.uploaded) continue;

            try {
                await this.uploadChunk(fileId, i);
            } catch (error) {
                throw error;
            }
        }

        // Complete upload
        await this.completeChunkedUpload(fileId);
    }

    /**
     * Upload individual chunk
     */
    async uploadChunk(fileId, chunkIndex) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        const chunk = upload.chunks[chunkIndex];
        const chunkData = upload.file.slice(chunk.start, chunk.end);

        const formData = new FormData();
        formData.append('chunk', chunkData);
        formData.append('chunkIndex', chunkIndex);
        formData.append('totalChunks', upload.chunks.length);
        formData.append('fileName', upload.name);
        formData.append('fileId', fileId);

        const response = await fetch(this.config.uploadEndpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Chunk upload failed: ${response.statusText}`);
        }

        chunk.uploaded = true;
        upload.currentChunk = chunkIndex + 1;
        upload.uploadedBytes = upload.chunks.slice(0, chunkIndex + 1).reduce((sum, c) => sum + c.size, 0);
        upload.progress = (upload.uploadedBytes / upload.size) * 100;
        upload.speed = this.calculateSpeed(upload.uploadedBytes, upload.startTime);
        upload.timeRemaining = this.calculateTimeRemaining(upload.uploadedBytes, upload.size, upload.speed);
        
        this.updateFileItem(fileId);
        this.updateOverallProgress();
    }

    /**
     * Complete chunked upload
     */
    async completeChunkedUpload(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        const formData = new FormData();
        formData.append('fileId', fileId);
        formData.append('fileName', upload.name);
        formData.append('totalChunks', upload.chunks.length);
        formData.append('complete', 'true');

        const response = await fetch(this.config.uploadEndpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Complete upload failed: ${response.statusText}`);
        }

        const result = await response.json();
        upload.response = result;
    }

    /**
     * Validate file on server
     */
    async validateFileOnServer(file) {
        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(this.config.validationEndpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: formData
            });

            if (response.ok) {
                return await response.json();
            } else {
                return { valid: false, error: 'Validation failed' };
            }
        } catch (error) {
            return { valid: false, error: 'Validation error' };
        }
    }

    /**
     * Update file item UI
     */
    updateFileItem(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        const fileItem = this.container.querySelector(`[data-file-id="${fileId}"]`);
        if (!fileItem) return;

        fileItem.innerHTML = this.renderFileItem(upload);
    }

    /**
     * Render file item
     */
    renderFileItem(upload) {
        const fileIcon = this.getFileIcon(upload.extension);
        const progressBar = this.renderProgressBar(upload.progress, upload.state);
        const fileInfo = this.renderFileInfo(upload);
        const actions = this.renderFileActions(upload);

        return `
            <div class="file-item-content">
                <div class="file-preview">
                    ${this.config.enablePreview && this.isImageFile(upload.extension) ? 
                        `<img src="${URL.createObjectURL(upload.file)}" alt="${upload.name}" class="file-thumbnail">` :
                        `<div class="file-icon"><i class="fas ${fileIcon}"></i></div>`
                    }
                </div>
                <div class="file-details">
                    <div class="file-name">${upload.name}</div>
                    <div class="file-info">${fileInfo}</div>
                    ${progressBar}
                </div>
                <div class="file-actions">
                    ${actions}
                </div>
            </div>
        `;
    }

    /**
     * Render progress bar
     */
    renderProgressBar(progress, state) {
        let statusText = '';
        let statusClass = '';

        switch (state) {
            case this.uploadStates.UPLOADING:
                statusText = this.texts.uploading;
                statusClass = 'uploading';
                break;
            case this.uploadStates.VALIDATING:
                statusText = this.texts.validating;
                statusClass = 'validating';
                break;
            case this.uploadStates.COMPLETED:
                statusText = this.texts.uploadComplete;
                statusClass = 'completed';
                break;
            case this.uploadStates.FAILED:
                statusText = this.texts.uploadFailed;
                statusClass = 'failed';
                break;
            case this.uploadStates.CANCELLED:
                statusText = this.texts.uploadCancelled;
                statusClass = 'cancelled';
                break;
            case this.uploadStates.PAUSED:
                statusText = this.texts.paused;
                statusClass = 'paused';
                break;
            default:
                statusText = this.texts.queued;
                statusClass = 'queued';
        }

        return `
            <div class="file-progress">
                <div class="progress-bar">
                    <div class="progress-fill ${statusClass}" style="width: ${progress}%"></div>
                </div>
                <div class="progress-info">
                    <span class="progress-text">${statusText}</span>
                    <span class="progress-percentage">${Math.round(progress)}%</span>
                </div>
            </div>
        `;
    }

    /**
     * Render file info
     */
    renderFileInfo(upload) {
        const size = this.formatFileSize(upload.size);
        const type = this.getFileTypeLabel(upload.extension);
        
        return `
            <span class="file-size">${size}</span>
            <span class="file-type">${type}</span>
        `;
    }

    /**
     * Render file actions
     */
    renderFileActions(upload) {
        let actions = '';

        switch (upload.state) {
            case this.uploadStates.QUEUED:
                actions = `
                    <button type="button" class="btn-start-upload" title="${this.texts.startUpload}">
                        <i class="fas fa-play"></i>
                    </button>
                    <button type="button" class="btn-remove-file" title="${this.texts.removeFile}">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                break;
            case this.uploadStates.UPLOADING:
                actions = `
                    <button type="button" class="btn-pause-upload" title="${this.texts.pauseUpload}">
                        <i class="fas fa-pause"></i>
                    </button>
                    <button type="button" class="btn-cancel-upload" title="${this.texts.cancelUpload}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                break;
            case this.uploadStates.PAUSED:
                actions = `
                    <button type="button" class="btn-resume-upload" title="${this.texts.resumeUpload}">
                        <i class="fas fa-play"></i>
                    </button>
                    <button type="button" class="btn-cancel-upload" title="${this.texts.cancelUpload}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                break;
            case this.uploadStates.COMPLETED:
                actions = `
                    ${this.config.enablePreview ? `
                        <button type="button" class="btn-preview-file" title="${this.texts.preview}">
                            <i class="fas fa-eye"></i>
                        </button>
                    ` : ''}
                    <button type="button" class="btn-remove-file" title="${this.texts.removeFile}">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                break;
            case this.uploadStates.FAILED:
                actions = `
                    <button type="button" class="btn-retry-upload" title="${this.texts.retry}">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button type="button" class="btn-remove-file" title="${this.texts.removeFile}">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                break;
            default:
                actions = `
                    <button type="button" class="btn-remove-file" title="${this.texts.removeFile}">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
        }

        return actions;
    }

    /**
     * Update file list
     */
    updateFileList() {
        const fileListContent = this.container.querySelector('.file-list-content');
        if (!fileListContent) return;

        if (this.uploads.size === 0) {
            fileListContent.innerHTML = `
                <div class="no-files">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Belum ada file yang dipilih</p>
                </div>
            `;
            return;
        }

        fileListContent.innerHTML = Array.from(this.uploads.values()).map(upload => {
            return `
                <div class="file-item" data-file-id="${upload.id}">
                    ${this.renderFileItem(upload)}
                </div>
            `;
        }).join('');
    }

    /**
     * Update bulk actions
     */
    updateBulkActions() {
        const hasFiles = this.uploads.size > 0;
        const hasUploading = Array.from(this.uploads.values()).some(u => u.state === this.uploadStates.UPLOADING);
        const hasPaused = Array.from(this.uploads.values()).some(u => u.state === this.uploadStates.PAUSED);
        const hasQueued = Array.from(this.uploads.values()).some(u => u.state === this.uploadStates.QUEUED);

        const startAllBtn = this.container.querySelector('.btn-start-all');
        const pauseAllBtn = this.container.querySelector('.btn-pause-all');
        const cancelAllBtn = this.container.querySelector('.btn-cancel-all');
        const clearAllBtn = this.container.querySelector('.btn-clear-all');
        const fileCount = this.container.querySelector('.file-count');

        if (fileCount) {
            fileCount.textContent = `${this.uploads.size} ${this.texts.filesSelected}`;
        }

        if (startAllBtn) {
            startAllBtn.disabled = !hasFiles || !hasQueued;
        }
        if (pauseAllBtn) {
            pauseAllBtn.disabled = !hasFiles || !hasUploading;
        }
        if (cancelAllBtn) {
            cancelAllBtn.disabled = !hasFiles;
        }
        if (clearAllBtn) {
            clearAllBtn.disabled = !hasFiles;
        }
    }

    /**
     * Update overall progress
     */
    updateOverallProgress() {
        const uploads = Array.from(this.uploads.values());
        if (uploads.length === 0) return;

        const totalSize = uploads.reduce((sum, upload) => sum + upload.size, 0);
        const uploadedSize = uploads.reduce((sum, upload) => sum + upload.uploadedBytes, 0);
        const overallProgress = totalSize > 0 ? (uploadedSize / totalSize) * 100 : 0;

        const progressContainer = this.container.querySelector('#upload-progress');
        const progressFill = this.container.querySelector('.progress-fill');
        const progressPercentage = this.container.querySelector('.progress-percentage');
        const uploadedFiles = this.container.querySelector('.uploaded-files');
        const totalFiles = this.container.querySelector('.total-files');
        const uploadSpeed = this.container.querySelector('.upload-speed');
        const timeRemaining = this.container.querySelector('.time-remaining');

        if (progressContainer) {
            progressContainer.style.display = uploads.some(u => u.state === this.uploadStates.UPLOADING) ? 'block' : 'none';
        }

        if (progressFill) {
            progressFill.style.width = `${overallProgress}%`;
        }

        if (progressPercentage) {
            progressPercentage.textContent = `${Math.round(overallProgress)}%`;
        }

        if (uploadedFiles) {
            uploadedFiles.textContent = uploads.filter(u => u.state === this.uploadStates.COMPLETED).length;
        }

        if (totalFiles) {
            totalFiles.textContent = uploads.length;
        }

        // Calculate overall speed and time remaining
        const activeUploads = uploads.filter(u => u.state === this.uploadStates.UPLOADING);
        if (activeUploads.length > 0) {
            const totalSpeed = activeUploads.reduce((sum, upload) => sum + upload.speed, 0);
            const remainingBytes = totalSize - uploadedSize;
            const remainingTime = totalSpeed > 0 ? remainingBytes / totalSpeed : 0;

            if (uploadSpeed) {
                uploadSpeed.textContent = `${this.formatFileSize(totalSpeed)}/s`;
            }

            if (timeRemaining) {
                timeRemaining.textContent = this.formatTime(remainingTime);
            }
        }
    }

    /**
     * Process upload queue
     */
    processUploadQueue() {
        if (this.isProcessingQueue || this.uploadQueue.length === 0) return;
        if (this.activeUploads >= this.maxConcurrentUploads) return;

        this.isProcessingQueue = true;
        
        while (this.uploadQueue.length > 0 && this.activeUploads < this.maxConcurrentUploads) {
            const fileId = this.uploadQueue.shift();
            this.startUpload(fileId);
        }

        this.isProcessingQueue = false;
    }

    /**
     * Bulk action methods
     */
    startAllUploads() {
        this.uploads.forEach((upload, fileId) => {
            if (upload.state === this.uploadStates.QUEUED) {
                this.startUpload(fileId);
            }
        });
    }

    pauseAllUploads() {
        this.uploads.forEach((upload, fileId) => {
            if (upload.state === this.uploadStates.UPLOADING) {
                this.pauseUpload(fileId);
            }
        });
    }

    cancelAllUploads() {
        this.uploads.forEach((upload, fileId) => {
            if ([this.uploadStates.UPLOADING, this.uploadStates.PAUSED].includes(upload.state)) {
                this.cancelUpload(fileId);
            }
        });
    }

    clearAllFiles() {
        if (confirm(this.texts.confirmRemove)) {
            this.uploads.forEach((upload, fileId) => {
                this.removeFile(fileId);
            });
        }
    }

    /**
     * Individual file action methods
     */
    pauseUpload(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        if (upload.xhr) {
            upload.xhr.abort();
        }

        upload.state = this.uploadStates.PAUSED;
        this.updateFileItem(fileId);
    }

    cancelUpload(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        if (upload.xhr) {
            upload.xhr.abort();
        }

        upload.state = this.uploadStates.CANCELLED;
        upload.progress = 0;
        this.updateFileItem(fileId);
    }

    removeFile(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        // Cancel ongoing upload
        if (upload.xhr) {
            upload.xhr.abort();
        }

        // Remove from uploads
        this.uploads.delete(fileId);
        
        // Remove from queue if queued
        const queueIndex = this.uploadQueue.indexOf(fileId);
        if (queueIndex > -1) {
            this.uploadQueue.splice(queueIndex, 1);
        }

        // Update UI
        this.updateFileList();
        this.updateBulkActions();
        this.updateOverallProgress();
    }

    previewFile(fileId) {
        const upload = this.uploads.get(fileId);
        if (!upload) return;

        // Implementation for file preview
        console.log('Preview file:', upload.name);
    }

    /**
     * Utility methods
     */
    generateFileId(file) {
        return `${file.name}-${file.size}-${file.lastModified}`;
    }

    getFileIcon(extension) {
        if (this.isImageFile(extension)) return 'fa-file-image';
        if (extension === 'pdf') return 'fa-file-pdf';
        if (['xls', 'xlsx'].includes(extension)) return 'fa-file-excel';
        if (['doc', 'docx'].includes(extension)) return 'fa-file-word';
        if (extension === 'csv') return 'fa-file-csv';
        if (extension === 'txt') return 'fa-file-alt';
        return 'fa-file';
    }

    getFileTypeLabel(extension) {
        if (this.isImageFile(extension)) return 'Gambar';
        if (extension === 'pdf') return 'PDF';
        if (['xls', 'xlsx'].includes(extension)) return 'Excel';
        if (['doc', 'docx'].includes(extension)) return 'Word';
        if (extension === 'csv') return 'CSV';
        if (extension === 'txt') return 'Teks';
        return 'File';
    }

    isImageFile(extension) {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    formatTime(seconds) {
        if (seconds < 60) return `${Math.round(seconds)}s`;
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.round(seconds % 60);
        return `${minutes}m ${remainingSeconds}s`;
    }

    calculateSpeed(uploadedBytes, startTime) {
        const elapsed = (new Date() - startTime) / 1000;
        return elapsed > 0 ? uploadedBytes / elapsed : 0;
    }

    calculateTimeRemaining(uploadedBytes, totalBytes, speed) {
        if (speed === 0) return null;
        const remainingBytes = totalBytes - uploadedBytes;
        return remainingBytes / speed;
    }

    showValidationResults(results) {
        // Implementation for showing validation results
        console.log('Validation results:', results);
    }

    closeValidationModal() {
        const modal = this.container.querySelector('#validation-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    continueUpload() {
        this.closeValidationModal();
        // Continue with upload process
    }

    /**
     * Destroy instance
     */
    destroy() {
        // Cancel all uploads
        this.uploads.forEach((upload, fileId) => {
            if (upload.xhr) {
                upload.xhr.abort();
            }
        });

        // Clear uploads
        this.uploads.clear();
        this.uploadQueue = [];

        // Remove container
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SakipFileUpload;
} else if (typeof window !== 'undefined') {
    window.SakipFileUpload = SakipFileUpload;
}