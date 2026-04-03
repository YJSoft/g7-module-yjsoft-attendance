import '../css/main.css';

const MODULE_IDENTIFIER = 'yjsoft-attendance';

let clockTimer: ReturnType<typeof setInterval> | null = null;

/**
 * Format current date/time as YYYY-MM-DD HH:mm:ss
 */
function formatDateTime(): string {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

/**
 * Start the clock timer — updates _local.currentTime every second
 */
function startClock(): void {
    const G7Core = (window as any).G7Core;
    if (!G7Core?.state?.setLocal) return;

    // Set initial time immediately
    G7Core.state.setLocal('currentTime', formatDateTime());

    // Update every second
    clockTimer = setInterval(() => {
        (window as any).G7Core?.state?.setLocal('currentTime', formatDateTime());
    }, 1000);
}

/**
 * Stop the clock timer
 */
function stopClock(): void {
    if (clockTimer !== null) {
        clearInterval(clockTimer);
        clockTimer = null;
    }
}

/**
 * Initialize random greeting from settings data
 */
function initRandomGreeting(params: Record<string, any>): void {
    const G7Core = (window as any).G7Core;
    if (!G7Core?.state?.setLocal) return;

    const greetings = params.greetings as string[] | undefined;
    if (greetings && greetings.length > 0) {
        const randomIndex = Math.floor(Math.random() * greetings.length);
        G7Core.state.setLocal('greeting', greetings[randomIndex]);
    }
}

/**
 * Handler: start clock timer (called on mount)
 */
function startClockHandler(): void {
    startClock();
}

/**
 * Handler: stop clock timer (called on unmount)
 */
function stopClockHandler(): void {
    stopClock();
}

/**
 * Handler map for this module
 */
const handlerMap: Record<string, (...args: any[]) => void> = {
    startClock: startClockHandler,
    stopClock: stopClockHandler,
    initRandomGreeting: initRandomGreeting,
};

export function initModule(): void {
    const registerHandlers = () => {
        const actionDispatcher = (window as any).G7Core?.getActionDispatcher?.();

        if (actionDispatcher) {
            Object.entries(handlerMap).forEach(([name, handler]) => {
                const fullName = `${MODULE_IDENTIFIER}.${name}`;
                actionDispatcher.registerHandler(fullName, handler);
            });
            console.log(`[Module:${MODULE_IDENTIFIER}] Handlers registered`);
        } else {
            // ActionDispatcher not ready yet — retry
            setTimeout(registerHandlers, 100);
        }
    };

    if (document.readyState === 'complete') {
        registerHandlers();
    } else {
        window.addEventListener('load', registerHandlers);
    }
}

// IIFE build — execute immediately
initModule();
