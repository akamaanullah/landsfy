        // --- Theme Toggler Logic (Light is Default) ---
        (function() {
            const htmlElement = document.documentElement;
            const currentTheme = localStorage.getItem('theme') || 'light';
            
            // 1. Immediate Apply (To avoid flash)
            if(htmlElement) {
                htmlElement.setAttribute('data-theme', currentTheme);
            }

            // 2. DOM-Ready specific logic
            document.addEventListener('DOMContentLoaded', () => {
                const themeToggleBtn = document.getElementById('themeToggle');
                const themeIcon = document.getElementById('themeIcon');

                function updateThemeIcon(theme) {
                    if(!themeIcon) return;
                    if (theme === 'dark') {
                        themeIcon.classList.remove('fa-moon');
                        themeIcon.classList.add('fa-sun');
                    } else {
                        themeIcon.classList.remove('fa-sun');
                        themeIcon.classList.add('fa-moon');
                    }
                }

                // Initial Icon State
                updateThemeIcon(currentTheme);

                if(themeToggleBtn) {
                    themeToggleBtn.addEventListener('click', () => {
                        const currentTheme = htmlElement.getAttribute('data-theme');
                        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                        
                        // 1. Local Storage (Immediate)
                        htmlElement.setAttribute('data-theme', newTheme);
                        localStorage.setItem('theme', newTheme);
                        updateThemeIcon(newTheme);

                        // 2. Server Side (If logged in)
                        if (window.IS_LOGGED_IN) {
                            const formData = new FormData();
                            formData.append('key', 'user_theme');
                            formData.append('value', newTheme);
                            
                            fetch(window.BASE_PATH + 'includes/api/save_user_setting.php', {
                                method: 'POST',
                                body: formData
                            }).catch(err => console.error('Failed to sync theme to server', err));
                        }
                    });
                }
            });
        })();

        /**
         * Global Image Utilities
         */
        window.getImageUrl = function(path) {
            if (!path) return '';
            if (path.startsWith('http')) return path;
            return '../' + path;
        };

        window.handleImageError = function(img, type = 'property') {
            img.onerror = null;
            img.src = '';
            let icon = 'fa-solid fa-house';
            if (type === 'user' || type === 'agent') icon = 'fa-solid fa-user';
            if (type === 'agency') icon = 'fa-solid fa-building';
            
            const placeholderClass = (type === 'property' || type === 'card') ? 'card-img-placeholder' : 'generic-img-placeholder';
            img.outerHTML = `<div class="${placeholderClass}"><i class="${icon}"></i></div>`;
        };

        // --- Premium Tab Controller (Sliding Indicator) ---
        const tabsContainer = document.getElementById('propertyTabs');
        if(tabsContainer) {
            const tabs = tabsContainer.querySelectorAll('.tab-btn');
            const indicator = document.getElementById('tabIndicator');
            const propertyItems = document.querySelectorAll('.property-item[data-type]');

            function updateIndicator(activeTab) {
                if(!indicator || !activeTab) return;
                indicator.style.width = `${activeTab.offsetWidth}px`;
                indicator.style.transform = `translateX(${activeTab.offsetLeft - 6}px)`; // -6 for container padding
            }

            // Initialize indicator position
            const initialActiveTab = document.querySelector('.tab-btn.active');
            // Small timeout to ensure fonts/layout are loaded before calculating width
            setTimeout(() => updateIndicator(initialActiveTab), 100);

            window.addEventListener('resize', () => {
                const activeTab = document.querySelector('.tab-btn.active');
                updateIndicator(activeTab);
            });

            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    // Remove active class from all
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add to clicked
                    const currentTab = e.target;
                    currentTab.classList.add('active');
                    
                    // Move indicator
                    updateIndicator(currentTab);

                    // Filter logic
                    const filter = currentTab.getAttribute('data-target');
                    if(propertyItems.length) {
                        propertyItems.forEach(item => {
                            item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            item.style.opacity = '0';
                            item.style.transform = 'translateY(10px)';
                            
                            setTimeout(() => {
                                if (filter === 'all' || item.getAttribute('data-type') === filter) {
                                    item.style.display = 'flex';
                                    setTimeout(() => {
                                        item.style.opacity = '1';
                                        item.style.transform = 'translateY(0)';
                                    }, 50);
                                } else {
                                    item.style.display = 'none';
                                }
                            }, 300);
                        });
                    }
                });
            });
        }

        // --- Add Property Form Logic (Pills & Chips) ---
        // Handle generic pill/chip group selection
        function setupSelectableGroup(groupSelector, inputId, onChangeCallback = null) {
            const group = document.querySelector(groupSelector);
            if(!group) return;
            
            const buttons = group.querySelectorAll('.pill-btn, .chip-btn');
            const hiddenInput = inputId ? document.getElementById(inputId) : null;

            buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Remove active from all
                    buttons.forEach(b => b.classList.remove('active'));
                    // Add to clicked
                    btn.classList.add('active');
                    
                    const val = btn.dataset.value || btn.innerText.replace('+', '').trim();
                    
                    // Update hidden input if it exists
                    if(hiddenInput) {
                        hiddenInput.value = val;
                    }

                    // Trigger callback
                    if(onChangeCallback) {
                        onChangeCallback(val);
                    }
                });
            });
        }

        // Setup specific groups
        setupSelectableGroup('#purposeGroup', 'propertyPurpose');
        setupSelectableGroup('#bedsBathsSection .form-group:nth-of-type(1) .circle-chips', 'bedsInput'); // Beds
        setupSelectableGroup('#bedsBathsSection .form-group:nth-of-type(2) .circle-chips', 'bathsInput'); // Baths
        
        // Property Type Main -> Subtype Logic
        const typeGroupMain = document.querySelector('#typeGroupMain');
        if(typeGroupMain) {
            const typeBtns = typeGroupMain.querySelectorAll('.type-tab-btn');
            const subTypeHome = document.getElementById('subTypeHome');
            const subTypePlot = document.getElementById('subTypePlot');
            const subTypeCommercial = document.getElementById('subTypeCommercial');
            const bedsBathsSection = document.getElementById('bedsBathsSection');
            
            typeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update active state manually sine we aren't using the generic setup for this specific complex one
                    typeBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    // Function to fade in a section softly
                    const showSection = (el, displayType = 'block') => {
                        if (!el) return;
                        el.style.display = displayType;
                        el.style.opacity = '0';
                        setTimeout(() => el.style.opacity = '1', 10);
                    };

                    // Hide all subtypes first
                    if (subTypeHome) subTypeHome.style.display = 'none';
                    if (subTypePlot) subTypePlot.style.display = 'none';
                    if (subTypeCommercial) subTypeCommercial.style.display = 'none';

                    // Show/hide sub-types and filter Amenities Modal based on selection
                    const val = btn.dataset.value;
                    const modalTabs = document.querySelectorAll('#amenitiesModal .modal-tab-btn');
                    
                    const filterModal = (contextType) => {
                        let firstVisibleFound = false;
                        modalTabs.forEach(tab => {
                            const ctx = tab.getAttribute('data-context');
                            if (ctx === 'all' || ctx === contextType) {
                                tab.style.display = 'block';
                                if (!firstVisibleFound) {
                                    tab.click(); // Auto-activate the first relevant tab
                                    firstVisibleFound = true;
                                }
                            } else {
                                tab.style.display = 'none';
                                // Optional: Uncheck inputs in hidden tabs so they aren't saved accidentally
                                const targetPaneId = tab.getAttribute('data-target');
                                const pane = document.getElementById(targetPaneId);
                                if (pane) {
                                    const checkboxes = pane.querySelectorAll('input[type="checkbox"]');
                                    checkboxes.forEach(cb => cb.checked = false);
                                    const inputs = pane.querySelectorAll('input[type="number"], input[type="text"]');
                                    inputs.forEach(ip => ip.value = '');
                                }
                            }
                        });
                    };

                    if(val === 'home') {
                        showSection(subTypeHome);
                        showSection(bedsBathsSection, 'flex');
                        filterModal('home');
                    } else if(val === 'plots' || val === 'plot') {
                        showSection(subTypePlot);
                        if(bedsBathsSection) bedsBathsSection.style.display = 'none'; // hide beds/baths for plots
                        filterModal('plot');
                    } else if(val === 'commercial') {
                        showSection(subTypeCommercial);
                        showSection(bedsBathsSection, 'flex'); // some commercial types have bathrooms
                        filterModal('commercial');
                    }
                });
            });
            
            // Setup the sub-type chips to be clickable too
            setupSelectableGroup('#subTypeHome .chip-group', null);
            setupSelectableGroup('#subTypePlot .chip-group', null);
            setupSelectableGroup('#subTypeCommercial .chip-group', null);
        }

        // Dropzone interactions (Visual only for now)
        const dropzone = document.getElementById('imageDropzone');
        const fileInput = document.getElementById('fileInput');

        if(dropzone && fileInput) {
            // dropzone.addEventListener('click', () => fileInput.click()); // Removed as it conflicts with add-property.js
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Visual feedback
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
            });
            
            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            });
            
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
            
            function handleFiles(files) {
                if(files.length > 0) {
                    const content = dropzone.querySelector('.dropzone-content');
                    content.innerHTML = `<i class="fa-solid fa-circle-check upload-icon" style="color: var(--success)"></i>
                                         <h4>${files.length} Image(s) Selected</h4>
                                         <p>Click to change selection</p>`;
                }
            }
        }

        // --- Video Dropzone Logic ---
        const videoDropzone = document.getElementById('videoDropzone');
        const videoInput = document.getElementById('videoInput');

        if(videoDropzone && videoInput) {
            videoDropzone.addEventListener('click', () => videoInput.click());
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                videoDropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Visual feedback
            ['dragenter', 'dragover'].forEach(eventName => {
                videoDropzone.addEventListener(eventName, () => videoDropzone.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                videoDropzone.addEventListener(eventName, () => videoDropzone.classList.remove('dragover'), false);
            });
            
            videoDropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleVideoFiles(files);
            });
            
            videoInput.addEventListener('change', function() {
                handleVideoFiles(this.files);
            });
            
            function handleVideoFiles(files) {
                if(files.length > 0) {
                    const content = videoDropzone.querySelector('.dropzone-content');
                    content.innerHTML = `<i class="fa-solid fa-circle-check upload-icon" style="color: var(--success); font-size: 48px; margin-bottom: 8px;"></i>
                                         <h4 style="font-size: 16px;">Video Selected</h4>
                                         <p style="font-size: 12px;">${files[0].name}</p>`;
                }
            }
        }

        // --- Amenities Modal Logic ---
        const openAmenitiesModalBtn = document.getElementById('openAmenitiesModalBtn');
        const amenitiesModal = document.getElementById('amenitiesModal');
        const closeAmenitiesModalBtn = document.getElementById('closeAmenitiesModalBtn');
        const cancelAmenitiesBtn = document.getElementById('cancelAmenitiesBtn');
        const saveAmenitiesBtn = document.getElementById('saveAmenitiesBtn');
        const selectedAmenitiesDisplay = document.getElementById('selectedAmenitiesDisplay');
        const propertyAmenitiesInput = document.getElementById('propertyAmenitiesInput');

        if(openAmenitiesModalBtn && amenitiesModal) {
            
            // Open Modal
            openAmenitiesModalBtn.addEventListener('click', () => {
                amenitiesModal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            });

            // Close Modal
            const closeModal = () => {
                amenitiesModal.classList.remove('active');
                document.body.style.overflow = '';
            };

            if (closeAmenitiesModalBtn) closeAmenitiesModalBtn.addEventListener('click', closeModal);
            if (cancelAmenitiesBtn) cancelAmenitiesBtn.addEventListener('click', closeModal);
            
            // Close on outside click
            amenitiesModal.addEventListener('click', (e) => {
                if(e.target === amenitiesModal) {
                    closeModal();
                }
            });

            // Modal Tabs Logic
            const modalTabBtns = amenitiesModal.querySelectorAll('.modal-tab-btn');
            const tabPanes = amenitiesModal.querySelectorAll('.tab-pane');

            modalTabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active from all tabs and panes
                    modalTabBtns.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));

                    // Add active to clicked tab and corresponding pane
                    btn.classList.add('active');
                    const targetPaneId = btn.getAttribute('data-target');
                    const targetPane = document.getElementById(targetPaneId);
                    if (targetPane) targetPane.classList.add('active');
                });
            });

            // Save Amenities Logic
            if (saveAmenitiesBtn) {
                saveAmenitiesBtn.addEventListener('click', () => {
                const selectedAmenitiesData = []; // Array of {id, label, value}
                const displayLabels = []; // Just for showing on UI
                
                // 1. Collect checkbox toggles
                const checkboxes = amenitiesModal.querySelectorAll('.amenity-checkbox:checked');
                checkboxes.forEach(cb => {
                    selectedAmenitiesData.push({
                        id: cb.dataset.id,
                        label: cb.value,
                        value: '1'
                    });
                    displayLabels.push(cb.value);
                });

                // 2. Collect numeric inputs
                const numberInputs = amenitiesModal.querySelectorAll('.amenity-input');
                numberInputs.forEach(input => {
                    if(input.value.trim() !== '') {
                        const name = input.closest('.amenity-item').querySelector('.amenity-name').textContent;
                        selectedAmenitiesData.push({
                            id: input.dataset.id,
                            label: name,
                            value: input.value.trim()
                        });
                        displayLabels.push(`${name}: ${input.value}`);
                    }
                });

                // Update hidden input for form submission
                if(propertyAmenitiesInput) {
                    propertyAmenitiesInput.value = JSON.stringify(selectedAmenitiesData);
                }

                // Update Display on main form
                if(selectedAmenitiesDisplay) {
                    selectedAmenitiesDisplay.innerHTML = '';
                    if(displayLabels.length === 0) {
                        selectedAmenitiesDisplay.innerHTML = '<div class="empty-state-text" style="color: var(--text-secondary); font-size: 14px; font-style: italic;">No additional amenities selected.</div>';
                    } else {
                        displayLabels.forEach(lbl => {
                            const chip = document.createElement('button');
                            chip.type = 'button';
                            chip.className = 'chip-btn active';
                            chip.style.pointerEvents = 'none';
                            chip.innerHTML = lbl + ' <i class="fa-solid fa-check" style="margin-left: 4px; font-size: 12px;"></i>';
                            selectedAmenitiesDisplay.appendChild(chip);
                        });
                    }
                }

                closeModal();
            });
            } // Close if(saveAmenitiesBtn)
        }

        // --- Dynamic Contact Fields (Mobile Numbers) ---
        const addPhoneBtn = document.getElementById('addPhoneBtn');
        const mobileFieldsContainer = document.getElementById('mobileFieldsContainer');

        window.insertPhoneRow = function(addBtn) {
            const container = document.getElementById('mobileFieldsContainer');
            if(!container) return;
            const fieldContent = container.querySelector('.field-content');
            
            const newRow = document.createElement('div');
            newRow.className = 'phone-input-group';
            newRow.style.opacity = '0';
            newRow.style.transform = 'translateY(10px)';
            newRow.style.transition = 'all 0.3s ease';
            
            newRow.innerHTML = `
                <div class="country-prefix">
                    <img src="https://flagcdn.com/w20/pk.png" alt="PK">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <input type="tel" class="glass-input" name="property_contacts[]" placeholder="+92">
                <button type="button" class="remove-phone-btn">
                    <i class="fa-solid fa-minus"></i>
                </button>
            `;

            fieldContent.appendChild(newRow);

            // Animation tick
            setTimeout(() => {
                newRow.style.opacity = '1';
                newRow.style.transform = 'translateY(0)';
            }, 10);

            // Handle Removal
            const removeBtn = newRow.querySelector('.remove-phone-btn');
            removeBtn.addEventListener('click', () => {
                newRow.style.opacity = '0';
                newRow.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    newRow.remove();
                    // Optional: we'd need to track phoneCount globally if we wanted to show addBtn again
                    if(addBtn) addBtn.style.display = 'flex';
                }, 300);
            });

            return newRow;
        };

        if(addPhoneBtn && mobileFieldsContainer) {
            let phoneCount = 1;
            const MAX_PHONES = 3;

            addPhoneBtn.addEventListener('click', () => {
                if(phoneCount >= MAX_PHONES) return;
                phoneCount++;
                
                insertPhoneRow(addPhoneBtn);

                if(phoneCount >= MAX_PHONES) {
                    addPhoneBtn.style.display = 'none';
                }
            });
        }

        // --- My Properties Filtering Logic & Custom Dropdowns ---
        const customDropdowns = document.querySelectorAll('.custom-dropdown');
        const statCards = document.querySelectorAll('.stat-card');
        const propertyCards = document.querySelectorAll('.property-card');
        const searchInput = document.getElementById('propertySearch');

        if(propertyCards.length > 0) {
            
            function filterProperties() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const selectedType = document.getElementById('typeFilter') ? document.getElementById('typeFilter').value : 'all';
                const selectedPurpose = document.getElementById('purposeFilter') ? document.getElementById('purposeFilter').value : 'all';
                const selectedAgency = document.getElementById('agencyFilter') ? document.getElementById('agencyFilter').value : 'all';
                const activeStat = document.querySelector('.stat-card.active') ? document.querySelector('.stat-card.active').dataset.filter : 'all';

                // Price & Area logic
                const priceDropdown = document.getElementById('priceDropdown');
                const areaDropdown = document.getElementById('areaDropdown');
                
                let minP = -Infinity, maxP = Infinity;
                let minA = -Infinity, maxA = Infinity;

                if (priceDropdown) {
                    const inputs = priceDropdown.querySelectorAll('input');
                    minP = parseFloat(inputs[0].value) || -Infinity;
                    maxP = parseFloat(inputs[1].value) || Infinity;
                }
                if (areaDropdown) {
                    const inputs = areaDropdown.querySelectorAll('input');
                    minA = parseFloat(inputs[0].value) || -Infinity;
                    maxA = parseFloat(inputs[1].value) || Infinity;
                }

                propertyCards.forEach(card => {
                    const status = card.dataset.status;
                    const type = card.dataset.type;
                    const agency = card.dataset.agency || 'all';
                    const title = card.querySelector('.card-title').innerText.toLowerCase();
                    const location = card.querySelector('.card-location').innerText.toLowerCase();
                    
                    // Extract price and area for numeric filtering
                    const priceText = card.querySelector('.card-price').innerText.replace(/[^0-9.]/g, '');
                    const priceValue = parseFloat(priceText) || 0;
                    
                    const areaText = card.querySelector('.feature-item:last-child').innerText.replace(/[^0-9.]/g, '');
                    const areaValue = parseFloat(areaText) || 0;

                    const matchesSearch = title.includes(searchTerm) || location.includes(searchTerm);
                    const matchesStatus = (activeStat === 'all') || (status === activeStat);
                    const matchesType = (selectedType === 'all') || (type === selectedType);
                    const matchesAgency = (selectedAgency === 'all') || (agency === selectedAgency);
                    const matchesPrice = (priceValue >= minP && priceValue <= maxP);
                    const matchesArea = (areaValue >= minA && areaValue <= maxA);

                    if(matchesSearch && matchesStatus && matchesType && matchesAgency && matchesPrice && matchesArea) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            // Custom Dropdown Interactivity
            customDropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.dropdown-trigger');
                const menu = dropdown.querySelector('.dropdown-menu');
                const items = dropdown.querySelectorAll('.dropdown-item');
                const hiddenInput = dropdown.querySelector('input[type="hidden"]');
                const selectedText = dropdown.querySelector('.selected-text');

                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // Close other dropdowns
                    customDropdowns.forEach(d => {
                        if(d !== dropdown) d.classList.remove('open');
                    });
                    dropdown.classList.toggle('open');
                });

                // Prevent menu from closing when clicking inside if it contains inputs
                menu.addEventListener('click', (e) => {
                    if (e.target.tagName !== 'DIV' || !e.target.classList.contains('dropdown-item')) {
                        e.stopPropagation();
                    }
                });

                items.forEach(item => {
                    item.addEventListener('click', () => {
                        const val = item.dataset.value;
                        const text = item.innerText;
                        
                        if(hiddenInput) hiddenInput.value = val;
                        if(selectedText) selectedText.innerText = text;
                        
                        items.forEach(i => i.classList.remove('active'));
                        item.classList.add('active');
                        
                        dropdown.classList.remove('open');
                        filterProperties();
                    });
                });

                // Apply button for range filters
                const applyBtn = menu.querySelector('.btn-primary');
                if(applyBtn) {
                    applyBtn.addEventListener('click', () => {
                        dropdown.classList.remove('open');
                        filterProperties();
                    });
                }
            });

            // Close dropdowns on click outside
            window.addEventListener('click', () => {
                customDropdowns.forEach(d => d.classList.remove('open'));
            });

            // Stat Card Clicks
            statCards.forEach(card => {
                card.addEventListener('click', () => {
                    statCards.forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    filterProperties();
                });
            });

            // Input Listeners
            if(searchInput) searchInput.addEventListener('input', Landsfy.debounce(filterProperties, 300));
        }

        // --- Property Detail Gallery Slider ---
        const mainSlide = document.getElementById('mainSlide');
        const thumbnails = document.querySelectorAll('.thumb');
        const prevBtn = document.querySelector('.slide-nav.prev');
        const nextBtn = document.querySelector('.slide-nav.next');
        const counter = document.querySelector('.image-counter');

        if(mainSlide && thumbnails.length > 0) {
            let currentIndex = 0;

            function updateSlider(index) {
                currentIndex = index;
                const activeThumb = thumbnails[currentIndex];
                
                // Switch Image
                mainSlide.src = activeThumb.src.replace('w=200', 'w=1200');

                // Update Thumbnails
                thumbnails.forEach(t => t.classList.remove('active'));
                activeThumb.classList.add('active');

                // Update Counter
                if(counter) counter.innerText = `${currentIndex + 1} / ${thumbnails.length}`;

                // Scroll thumb into view
                activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }

            thumbnails.forEach((thumb, index) => {
                thumb.addEventListener('click', () => updateSlider(index));
            });

            if(prevBtn) {
                prevBtn.addEventListener('click', () => {
                    let newIndex = currentIndex - 1;
                    if(newIndex < 0) newIndex = thumbnails.length - 1;
                    updateSlider(newIndex);
                });
            }

            if(nextBtn) {
                nextBtn.addEventListener('click', () => {
                    let newIndex = currentIndex + 1;
                    if(newIndex >= thumbnails.length) newIndex = 0;
                    updateSlider(newIndex);
                });
            }
        }

/**
 * Global Toaster Notification
 * @param {string} message - The message to display
 * @param {string} type - 'success', 'error', or 'info'
 * @param {number} duration - Auto-close time in ms
 */
window.showToast = function(message, type = 'success', duration = 5000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Choose icon
    let iconClass = 'fa-solid fa-circle-check';
    if (type === 'error') iconClass = 'fa-solid fa-circle-exclamation';
    if (type === 'info') iconClass = 'fa-solid fa-info';

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="${iconClass}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
        <div class="toast-close">
            <i class="fa-solid fa-xmark"></i>
        </div>
    `;

    // Add to container
    container.appendChild(toast);

    // Close logic
    const closeToast = () => {
        toast.classList.add('removing');
        setTimeout(() => {
            toast.remove();
        }, 300);
    };

    toast.querySelector('.toast-close').addEventListener('click', closeToast);

    // Auto close
    if (duration > 0) {
        setTimeout(closeToast, duration);
    }
};

// --- Global Search Logic ---
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('globalSearchInput');
    const resultsOverlay = document.getElementById('search-results-overlay');
    let searchTimeout = null;

    if (searchInput && resultsOverlay) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                resultsOverlay.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`../includes/api/admin/global_search.php?q=${encodeURIComponent(query)}`);
                    const result = await response.json();

                    if (result.success) {
                        renderSearchResults(result.data);
                    }
                } catch (error) {
                    console.error('Search failed', error);
                }
            }, 300);
        });

        // Close search list on blur
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !resultsOverlay.contains(e.target)) {
                resultsOverlay.style.display = 'none';
            }
        });
    }

    function renderSearchResults(data) {
        let html = '';
        let hasResults = false;

        const sections = [
            { key: 'properties', label: 'Properties', icon: 'fa-house-chimney', link: 'property-detail.php?id=', field: 'title' },
            { key: 'users', label: 'Users', icon: 'fa-user', link: 'user-detail.php?id=', field: 'full_name' },
            { key: 'agencies', label: 'Agencies', icon: 'fa-buildings', link: 'agency-detail.php?id=', field: 'name' }
        ];

        sections.forEach(sec => {
            if (data[sec.key] && data[sec.key].length > 0) {
                hasResults = true;
                html += `<div style="margin-bottom: 15px;">
                            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--primary); margin-bottom: 8px; border-bottom: 1px solid var(--glass-border); padding-bottom: 4px;">${sec.label}</div>`;
                
                data[sec.key].forEach(item => {
                    html += `
                        <a href="${sec.link}${item.id}" style="display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 8px; text-decoration: none; transition: background 0.2s ease; margin-bottom: 4px;" class="search-result-item">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(108, 93, 211, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fa-solid ${sec.icon}"></i>
                            </div>
                            <div>
                                <div style="font-size: 14px; font-weight: 600; color: var(--text-primary); line-height: 1.2;">${item[sec.field]}</div>
                                <div style="font-size: 11px; opacity: 0.6; color: var(--text-secondary);">ID: #${item.id}</div>
                            </div>
                        </a>
                    `;
                });
                html += `</div>`;
            }
        });

        if (!hasResults) {
            html = '<div style="text-align: center; padding: 20px; opacity: 0.6;">No results found for your query.</div>';
        }

        resultsOverlay.innerHTML = html;
        resultsOverlay.style.display = 'block';

        // Add Hover Style
        const items = resultsOverlay.querySelectorAll('.search-result-item');
        items.forEach(item => {
            item.onmouseover = () => item.style.background = 'rgba(255,255,255,0.05)';
            item.onmouseout = () => item.style.background = 'transparent';
        });
    }

    // --- Global Notification Dropdown Logic ---
    const notifBell = document.getElementById('notifBell');
    const notifDropdown = document.getElementById('notifDropdown');

    if (notifBell && notifDropdown) {
        notifBell.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = notifDropdown.style.display === 'block';
            notifDropdown.style.display = isOpen ? 'none' : 'block';
            notifBell.classList.toggle('active', !isOpen);
        });

        document.addEventListener('click', (e) => {
            if (!notifBell.contains(e.target) && !notifDropdown.contains(e.target)) {
                notifDropdown.style.display = 'none';
                notifBell.classList.remove('active');
            }
        });
    }
});
