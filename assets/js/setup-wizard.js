document.addEventListener('DOMContentLoaded', () => {
    const wizardWrapper = document.querySelector('.usp-setup-wizard-wrap');
    if (!wizardWrapper || !window.UspCore) {
        return;
    }

    const wizard = {
        currentStep: 0,
        totalSteps: 0,
        maxReachedStep: 0, // Отслеживаем самый дальний пройденный шаг
        form: document.getElementById('usp-wizard-form'),
        nextButton: document.getElementById('usp-wizard-next'),
        prevButton: document.getElementById('usp-wizard-prev'),
        finishButton: document.getElementById('usp-wizard-finish'),
        panes: [],
        steps: [],
        l10n: window.uspL10n?.wizard || {},

        init() {
            this.panes = this.form.querySelectorAll('.usp-wizard-pane');
            this.steps = wizardWrapper.querySelectorAll('.usp-wizard-step');
            this.totalSteps = this.panes.length;

            this.updateButtons();
            this.bindEvents();
        },

        bindEvents() {
            this.nextButton.addEventListener('click', this.handleNext.bind(this));
            this.prevButton.addEventListener('click', this.handlePrev.bind(this));

            // Добавляем возможность кликать по иконкам шагов
            this.steps.forEach((stepEl, index) => {
                stepEl.addEventListener('click', () => {
                    // Разрешаем переход только на уже посещенные шаги
                    if (index <= this.maxReachedStep) {
                        this.currentStep = index;
                        this.updateView();
                    }
                });
            });
        },

        async handleNext(e) {
            e.preventDefault();
            if (this.nextButton.disabled) {
                return;
            }

            const saved = await this.saveStepData();
            if (saved) {
                if (this.currentStep < this.totalSteps - 1) {
                    this.currentStep++;
                    this.maxReachedStep = Math.max(this.maxReachedStep, this.currentStep);
                    this.updateView();
                } else {
                    // Это был последний шаг, перенаправляем на страницу настроек
                    const finishUrl = this.finishButton.href;
                    if (finishUrl) {
                        window.location.href = finishUrl;
                    }
                }
            }
        },

        handlePrev(e) {
            e.preventDefault();
            if (this.currentStep > 0) {
                this.currentStep--;
                this.updateView();
            }
        },

        updateView() {
            // Panes
            this.panes.forEach((pane, index) => {
                pane.classList.toggle('active', index === this.currentStep);
            });
            // Steps navigation
            this.steps.forEach((step, index) => {
                step.classList.toggle('active', index === this.currentStep);
                // Добавляем класс 'clickable' для пройденных шагов
                if (index <= this.maxReachedStep) {
                    step.classList.add('is-clickable');
                }
            });
            this.updateButtons();
        },

        updateButtons() {
            this.prevButton.style.display = this.currentStep > 0 ? 'inline-block' : 'none';

            if (this.currentStep === this.totalSteps - 1) {
                this.nextButton.textContent = this.l10n.finish || 'Finish';
            } else {
                this.nextButton.textContent = this.l10n.next || 'Next';
            }
        },

        async saveStepData() {
            const currentPane = this.panes[this.currentStep];
            const inputs = currentPane.querySelectorAll('input, select, textarea');
            const stepData = {};

            inputs.forEach(input => {
                if (input.name) {
                    stepData[input.name] = input.value;
                }
            });

            this.nextButton.disabled = true;
            this.nextButton.textContent = this.l10n.saving || 'Saving...';

            try {
                const response = await window.UspCore.api.post('/setup-wizard/save-step', { data: stepData });
                window.UspCore.ui.showAdminNotice(response.message, 'success', '#usp-wizard-notifications');
                return true;
            } catch (error) {
                const errorMessage = error.message || this.l10n.networkError || 'Network error';
                window.UspCore.ui.showAdminNotice(errorMessage, 'error', '#usp-wizard-notifications');
                return false;
            } finally {
                this.nextButton.disabled = false;
                this.updateButtons(); // Восстановить текст кнопки
            }
        }
    };

    wizard.init();
});