#!/bin/bash

# Path to your project
PROJECT_PATH="/home/u431417112/domains/scamfreeind.in/public_html"
PID_FILE="$PROJECT_PATH/storage/reverb.pid"
LOG_FILE="$PROJECT_PATH/storage/logs/reverb.log"

case "$1" in
    start)
        # Check if already running
        if [ -f "$PID_FILE" ]; then
            PID=$(cat "$PID_FILE")
            if ps -p $PID > /dev/null; then
                echo "Reverb is already running (PID: $PID)"
                exit 0
            else
                echo "PID file found but process is not running. Cleaning up..."
                rm "$PID_FILE"
            fi
        fi

        echo "Starting Reverb server..."
        cd "$PROJECT_PATH"
        nohup php artisan reverb:start >> "$LOG_FILE" 2>&1 &
        echo $! > "$PID_FILE"
        echo "Reverb started with PID: $(cat "$PID_FILE")"
        ;;
    stop)
        if [ -f "$PID_FILE" ]; then
            PID=$(cat "$PID_FILE")
            echo "Stopping Reverb (PID: $PID)..."
            kill $PID
            rm "$PID_FILE"
            echo "Stopped."
        else
            echo "Reverb is not running."
        fi
        ;;
    status)
        if [ -f "$PID_FILE" ]; then
            PID=$(cat "$PID_FILE")
            if ps -p $PID > /dev/null; then
                echo "Reverb is running (PID: $PID)"
            else
                echo "Reverb is NOT running (Stale PID file)"
            fi
        else
            echo "Reverb is NOT running"
        fi
        ;;
    *)
        echo "Usage: $0 {start|stop|status}"
        exit 1
        ;;
esac
