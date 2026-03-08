/**
 * Seat Locking System - Frontend Module
 *
 * This module provides a complete client-side implementation for:
 * - Seat selection with locking
 * - 5-minute countdown timer
 * - Real-time seat status polling
 * - Seat state management (available, locked, booked)
 *
 * No WebSocket or real-time services are used.
 * Uses REST APIs and periodic polling (3-5 seconds) to simulate real-time updates.
 */

class SeatLockingSystem {
    /**
     * Initialize the seat locking system.
     *
     * @param {Object} config Configuration object
     * @param {number} config.movieId - Movie ID
     * @param {string} config.showDate - Show date in YYYY-MM-DD format
     * @param {string} config.showTime - Show time in HH:MM format
     * @param {string} config.sessionId - Unique user session identifier
     * @param {number} config.pollInterval - Polling interval in milliseconds (default: 4000)
     * @param {number} config.lockDuration - Lock duration in seconds (default: 300)
     * @param {string} config.apiBaseUrl - Base URL for API calls (default: '/api')
     */
    constructor(config) {
        this.movieId = config.movieId;
        this.showDate = config.showDate;
        this.showTime = config.showTime;
        this.sessionId = config.sessionId;
        this.pollInterval = config.pollInterval || 4000; // 4 seconds
        this.lockDuration = config.lockDuration || 300; // 5 minutes
        this.apiBaseUrl = config.apiBaseUrl || '/api';

        // State management
        this.seats = new Map(); // Map of seat_number => seat object
        this.selectedSeats = new Map(); // Map of seat_number => lock info
        this.timers = new Map(); // Map of seat_number => interval id
        this.isPolling = false;
        this.pollTimerId = null;

        // DOM elements
        this.seatGridElement = null;
        this.seatLayoutCache = null;
    }

    /**
     * Initialize the UI and start polling.
     *
     * @param {HTMLElement} containerElement - Container element for seat grid
     * @returns {Promise<void>}
     */
    async init(containerElement) {
        this.seatGridElement = containerElement;

        try {
            // Generate seat layout
            await this.loadSeatLayout();

            // Fetch initial seat statuses
            await this.refreshSeatStatuses();

            // Render UI
            this.renderSeatGrid();

            // Start polling for seat status updates
            this.startPolling();

            console.log('Seat locking system initialized successfully');
        } catch (error) {
            console.error('Failed to initialize seat locking system:', error);
            throw error;
        }
    }

    /**
     * Load the standard seat layout from the API.
     * Seats are arranged in rows (A-Z) and columns (1-12).
     *
     * @returns {Promise<void>}
     */
    async loadSeatLayout() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/seat-layout`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Failed to load seat layout: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                this.seatLayoutCache = data.layout;
                console.log(`Loaded ${data.total_seats} seats`);
            } else {
                throw new Error(data.message || 'Unknown error loading seat layout');
            }
        } catch (error) {
            console.error('Error loading seat layout:', error);
            throw error;
        }
    }

    /**
     * Refresh seat statuses from the server.
     * Called periodically and on user action.
     *
     * @returns {Promise<void>}
     */
    async refreshSeatStatuses() {
        try {
            const response = await fetch(
                `${this.apiBaseUrl}/seats?movie_id=${this.movieId}&show_date=${this.showDate}&show_time=${this.showTime}`,
                {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            if (!response.ok) {
                throw new Error(`Failed to refresh seat statuses: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                // Update seat states from server
                data.seats.forEach(serverSeat => {
                    const localSeat = this.seats.get(serverSeat.seat_number) || {
                        seat_number: serverSeat.seat_number,
                    };

                    // Update status from server
                    localSeat.status = serverSeat.status;

                    if (serverSeat.status === 'locked') {
                        localSeat.locked_by_session = serverSeat.locked_by_session;
                        localSeat.remaining_seconds = serverSeat.remaining_seconds;
                        localSeat.expires_at = serverSeat.expires_at;

                        // Check if this seat is locked by current user
                        if (serverSeat.locked_by_session === this.sessionId) {
                            // Ensure timer is running
                            if (!this.timers.has(serverSeat.seat_number)) {
                                this.startCountdownTimer(serverSeat.seat_number);
                            }
                        }
                    }

                    this.seats.set(serverSeat.seat_number, localSeat);
                });

                // Update UI
                this.updateSeatGridUI();
            } else {
                throw new Error(data.message || 'Failed to refresh seat statuses');
            }
        } catch (error) {
            console.error('Error refreshing seat statuses:', error);
            // Continue polling even on error
        }
    }

    /**
     * Handle seat click event.
     * Toggles seat selection and manages locking.
     *
     * @param {string} seatNumber - Seat identifier (e.g., "A1")
     * @returns {Promise<void>}
     */
    async selectSeat(seatNumber) {
        const seat = this.seats.get(seatNumber);

        if (!seat) {
            console.error(`Seat ${seatNumber} not found`);
            return;
        }

        // Deselect if already selected
        if (this.selectedSeats.has(seatNumber)) {
            await this.deselectSeat(seatNumber);
            return;
        }

        // Cannot select booked seats
        if (seat.status === 'booked') {
            this.showNotification('This seat is already booked.', 'error');
            return;
        }

        // Cannot select seats locked by other users
        if (seat.status === 'locked' && seat.locked_by_session !== this.sessionId) {
            this.showNotification('This seat is currently locked by another user.', 'error');
            return;
        }

        // Lock the seat
        try {
            const response = await this.lockSeatOnServer(seatNumber);

            if (response.success) {
                // Update local state
                this.selectedSeats.set(seatNumber, {
                    locked_at: response.lock.locked_at,
                    expires_at: response.lock.expires_at,
                    remaining_seconds: response.remaining_seconds,
                });

                // Update seat status
                seat.status = 'locked';
                seat.locked_by_session = this.sessionId;
                seat.remaining_seconds = response.remaining_seconds;

                // Start countdown timer
                this.startCountdownTimer(seatNumber);

                // Update UI
                this.updateSeatGridUI();

                this.showNotification(`Seat ${seatNumber} selected. Locking for 5 minutes.`, 'success');
            } else {
                this.showNotification(`Failed to select seat: ${response.message}`, 'error');
            }
        } catch (error) {
            console.error(`Error selecting seat ${seatNumber}:`, error);
            this.showNotification('Failed to select seat. Please try again.', 'error');
        }
    }

    /**
     * Deselect a seat and release its lock.
     *
     * @param {string} seatNumber - Seat identifier
     * @returns {Promise<void>}
     */
    async deselectSeat(seatNumber) {
        try {
            // Release lock on server
            await this.releaseSeatOnServer(seatNumber);

            // Stop timer
            this.stopCountdownTimer(seatNumber);

            // Update local state
            this.selectedSeats.delete(seatNumber);

            const seat = this.seats.get(seatNumber);
            if (seat) {
                seat.status = 'available';
                seat.locked_by_session = null;
                seat.remaining_seconds = null;
            }

            // Update UI
            this.updateSeatGridUI();

            this.showNotification(`Seat ${seatNumber} deselected.`, 'info');
        } catch (error) {
            console.error(`Error deselecting seat ${seatNumber}:`, error);
            this.showNotification('Failed to deselect seat. Please try again.', 'error');
        }
    }

    /**
     * Lock a seat on the server.
     *
     * @param {string} seatNumber
     * @returns {Promise<Object>}
     */
    async lockSeatOnServer(seatNumber) {
        const response = await fetch(`${this.apiBaseUrl}/lock-seat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCsrfToken(),
            },
            body: JSON.stringify({
                movie_id: this.movieId,
                show_date: this.showDate,
                show_time: this.showTime,
                seat_number: seatNumber,
                session_id: this.sessionId,
            }),
        });

        if (!response.ok) {
            throw new Error(`Failed to lock seat: ${response.statusText}`);
        }

        return await response.json();
    }

    /**
     * Release a seat lock on the server.
     *
     * @param {string} seatNumber
     * @returns {Promise<Object>}
     */
    async releaseSeatOnServer(seatNumber) {
        const response = await fetch(`${this.apiBaseUrl}/release-seat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCsrfToken(),
            },
            body: JSON.stringify({
                movie_id: this.movieId,
                show_date: this.showDate,
                show_time: this.showTime,
                seat_number: seatNumber,
                session_id: this.sessionId,
            }),
        });

        if (!response.ok) {
            throw new Error(`Failed to release seat: ${response.statusText}`);
        }

        return await response.json();
    }

    /**
     * Start a countdown timer for a seat.
     * Updates UI every second showing remaining time.
     *
     * @param {string} seatNumber
     */
    startCountdownTimer(seatNumber) {
        // Don't start multiple timers for same seat
        if (this.timers.has(seatNumber)) {
            return;
        }

        let remaining = this.lockDuration;

        const intervalId = setInterval(() => {
            remaining--;

            if (remaining <= 0) {
                // Lock expired
                this.stopCountdownTimer(seatNumber);
                this.handleLockExpired(seatNumber);
                return;
            }

            // Update UI with remaining time
            const seat = this.seats.get(seatNumber);
            if (seat) {
                seat.remaining_seconds = remaining;
                this.updateSeatUI(seatNumber);
            }
        }, 1000);

        this.timers.set(seatNumber, intervalId);
        console.log(`Timer started for seat ${seatNumber}`);
    }

    /**
     * Stop countdown timer for a seat.
     *
     * @param {string} seatNumber
     */
    stopCountdownTimer(seatNumber) {
        const intervalId = this.timers.get(seatNumber);
        if (intervalId) {
            clearInterval(intervalId);
            this.timers.delete(seatNumber);
            console.log(`Timer stopped for seat ${seatNumber}`);
        }
    }

    /**
     * Handle expired seat lock.
     * Called when 5-minute timer expires.
     *
     * @param {string} seatNumber
     */
    async handleLockExpired(seatNumber) {
        console.warn(`Lock expired for seat ${seatNumber}`);

        // Remove from selected seats
        this.selectedSeats.delete(seatNumber);

        // Update seat status
        const seat = this.seats.get(seatNumber);
        if (seat) {
            seat.status = 'available';
            seat.locked_by_session = null;
            seat.remaining_seconds = null;
        }

        // Update UI
        this.updateSeatGridUI();

        this.showNotification(`Lock expired for seat ${seatNumber}. Please select again if needed.`, 'warning');
    }

    /**
     * Get all currently selected seats.
     *
     * @returns {Array<string>} Array of seat numbers
     */
    getSelectedSeats() {
        return Array.from(this.selectedSeats.keys());
    }

    /**
     * Render the complete seat grid UI.
     * Should be called once during initialization.
     */
    renderSeatGrid() {
        if (!this.seatGridElement || !this.seatLayoutCache) {
            console.error('Cannot render seat grid: element or layout not available');
            return;
        }

        // Clear existing content
        this.seatGridElement.innerHTML = '';

        // Group seats by row
        const rowMap = new Map();
        this.seatLayoutCache.forEach(seatLayout => {
            if (!rowMap.has(seatLayout.row)) {
                rowMap.set(seatLayout.row, []);
            }
            rowMap.set(seatLayout.row, [...rowMap.get(seatLayout.row), seatLayout]);
        });

        // Create screen indicator
        const screenDiv = document.createElement('div');
        screenDiv.className = 'seat-grid-screen';
        screenDiv.textContent = '🎬 SCREEN 🎬';
        this.seatGridElement.appendChild(screenDiv);

        // Create rows
        rowMap.forEach((seatsInRow, row) => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'seat-row';
            rowDiv.dataset.row = row;

            // Row label
            const labelSpan = document.createElement('span');
            labelSpan.className = 'row-label';
            labelSpan.textContent = row;
            rowDiv.appendChild(labelSpan);

            // Seats in row
            const seatsContainer = document.createElement('div');
            seatsContainer.className = 'seats-container';

            seatsInRow.forEach(seatLayout => {
                const seatDiv = document.createElement('div');
                seatDiv.className = 'seat';
                seatDiv.dataset.seatNumber = seatLayout.seat_number;
                seatDiv.title = `Seat ${seatLayout.seat_number}`;

                // Update seat state from map
                const seat = this.seats.get(seatLayout.seat_number);
                if (seat) {
                    this.applySeatStatus(seatDiv, seat);
                } else {
                    // Initialize seat if not exists
                    const newSeat = {
                        seat_number: seatLayout.seat_number,
                        status: 'available',
                    };
                    this.seats.set(seatLayout.seat_number, newSeat);
                    seatDiv.classList.add('available');
                }

                // Add click handler
                seatDiv.addEventListener('click', () => this.selectSeat(seatLayout.seat_number));

                seatsContainer.appendChild(seatDiv);
            });

            rowDiv.appendChild(seatsContainer);
            this.seatGridElement.appendChild(rowDiv);
        });

        // Add legend
        this.addLegend();
    }

    /**
     * Update seat grid UI without re-rendering the entire grid.
     * More efficient than full re-render.
     */
    updateSeatGridUI() {
        this.seats.forEach((seat, seatNumber) => {
            this.updateSeatUI(seatNumber);
        });
    }

    /**
     * Update a single seat's UI element.
     *
     * @param {string} seatNumber
     */
    updateSeatUI(seatNumber) {
        const seatElement = this.seatGridElement.querySelector(
            `[data-seat-number="${seatNumber}"]`
        );

        if (!seatElement) {
            return;
        }

        const seat = this.seats.get(seatNumber);
        if (!seat) {
            return;
        }

        // Remove all status classes
        seatElement.classList.remove('available', 'locked', 'booked');

        // Apply new status
        this.applySeatStatus(seatElement, seat);
    }

    /**
     * Apply status classes and attributes to a seat element.
     *
     * @param {HTMLElement} seatElement
     * @param {Object} seat
     */
    applySeatStatus(seatElement, seat) {
        seatElement.classList.add(seat.status);

        // Add status indicator for locked seats
        if (seat.status === 'locked') {
            if (seat.locked_by_session === this.sessionId) {
                seatElement.classList.add('locked-by-me');
                seatElement.title = `Your lock (${seat.remaining_seconds}s remaining)`;
            } else {
                seatElement.classList.add('locked-by-other');
                seatElement.title = 'Locked by another user';
            }
        } else if (seat.status === 'booked') {
            seatElement.title = 'Booked';
            seatElement.style.cursor = 'not-allowed';
        } else {
            seatElement.title = `Seat ${seat.seat_number} - Click to select`;
            seatElement.style.cursor = 'pointer';
        }
    }

    /**
     * Add legend to show seat status colors.
     */
    addLegend() {
        const legendDiv = document.createElement('div');
        legendDiv.className = 'seat-legend';
        legendDiv.innerHTML = `
            <div class="legend-item">
                <span class="seat available"></span>
                <span>Available</span>
            </div>
            <div class="legend-item">
                <span class="seat locked locked-by-me"></span>
                <span>Your Lock</span>
            </div>
            <div class="legend-item">
                <span class="seat locked locked-by-other"></span>
                <span>Locked by Other</span>
            </div>
            <div class="legend-item">
                <span class="seat booked"></span>
                <span>Booked</span>
            </div>
        `;
        this.seatGridElement.appendChild(legendDiv);
    }

    /**
     * Start polling for seat status updates.
     * Polls every 3-5 seconds to refresh seat states.
     */
    startPolling() {
        if (this.isPolling) {
            return;
        }

        this.isPolling = true;
        console.log(`Started polling seat statuses every ${this.pollInterval}ms`);

        this.pollTimerId = setInterval(async () => {
            await this.refreshSeatStatuses();
        }, this.pollInterval);
    }

    /**
     * Stop polling for seat status updates.
     */
    stopPolling() {
        if (this.pollTimerId) {
            clearInterval(this.pollTimerId);
            this.pollTimerId = null;
            this.isPolling = false;
            console.log('Stopped polling seat statuses');
        }
    }

    /**
     * Cleanup: release all locks and stop timers.
     * Call this when user leaves the booking page.
     *
     * @returns {Promise<void>}
     */
    async cleanup() {
        console.log('Cleaning up seat locking system...');

        // Stop polling
        this.stopPolling();

        // Stop all timers
        this.timers.forEach(intervalId => clearInterval(intervalId));
        this.timers.clear();

        // Release all locks on server
        try {
            await this.releaseAllSeatsOnServer();
        } catch (error) {
            console.error('Error releasing all seats:', error);
        }

        console.log('Cleanup completed');
    }

    /**
     * Release all seat locks for this session on the server.
     *
     * @returns {Promise<Object>}
     */
    async releaseAllSeatsOnServer() {
        const response = await fetch(`${this.apiBaseUrl}/release-all-seats`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': this.getCsrfToken(),
            },
            body: JSON.stringify({
                session_id: this.sessionId,
            }),
        });

        if (!response.ok) {
            throw new Error(`Failed to release all seats: ${response.statusText}`);
        }

        return await response.json();
    }

    /**
     * Show a notification to the user.
     * Implement this based on your UI framework.
     *
     * @param {string} message
     * @param {string} type - 'success', 'error', 'warning', 'info'
     */
    showNotification(message, type = 'info') {
        // Create a simple notification div
        const notif = document.createElement('div');
        notif.className = `notification notification-${type}`;
        notif.textContent = message;
        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            background-color: ${
                type === 'success' ? '#10b981' :
                type === 'error' ? '#ef4444' :
                type === 'warning' ? '#f59e0b' :
                '#3b82f6'
            };
            color: white;
            z-index: 9999;
            max-width: 400px;
        `;

        document.body.appendChild(notif);

        // Auto-remove after 5 seconds
        setTimeout(() => notif.remove(), 5000);
    }

    /**
     * Get CSRF token from the page.
     * Assumes token is in meta tag or input field.
     *
     * @returns {string}
     */
    getCsrfToken() {
        // Try meta tag first
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            return meta.getAttribute('content');
        }

        // Try input field
        const input = document.querySelector('input[name="_token"]');
        if (input) {
            return input.value;
        }

        return '';
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SeatLockingSystem;
}
