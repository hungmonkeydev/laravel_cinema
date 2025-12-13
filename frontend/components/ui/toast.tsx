"use client";

import React from "react";

export type ToastActionElement = React.ReactElement | null;

export interface ToastProps {
  id?: string;
  open?: boolean;
  onOpenChange?: (open: boolean) => void;
  title?: React.ReactNode;
  description?: React.ReactNode;
  action?: ToastActionElement;
  duration?: number;
  className?: string;
}

/**
 * Minimal Toast component used by use-toast hook.
 * You can replace styling / behavior later with your preferred toast UI.
 */
export default function Toast({
  open = true,
  title,
  description,
  action,
  onOpenChange,
  className = "",
}: ToastProps) {
  if (!open) return null;

  return (
    <div
      role="status"
      aria-live="polite"
      className={`max-w-md w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-md p-3 flex items-start gap-3 ${className}`}
    >
      <div className="flex-1">
        {title && <div className="font-semibold text-sm text-foreground">{title}</div>}
        {description && <div className="text-xs text-muted-foreground mt-1">{description}</div>}
      </div>

      {action && <div className="ml-2 flex items-center">{action}</div>}

      <button
        aria-label="Close toast"
        onClick={() => onOpenChange?.(false)}
        className="ml-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
      >
        ✕
      </button>
    </div>
  );
}