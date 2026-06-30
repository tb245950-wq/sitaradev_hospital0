# 🎨 Alert System - Visual Preview

## Alert Designs

### Success Alert
```
┌─────────────────────────────────────────────────┐
│ ✓ Data Saved Successfully          ✕           │
├─────────────────────────────────────────────────┤
│ Your changes have been saved                    │
├─────────────────────────────────────────────────┤
│ [████░░░░░░░░░░░░░░░] 1.5s                     │
└─────────────────────────────────────────────────┘
```
- **Color**: Green (#10b981)
- **Duration**: 3 seconds
- **Icon**: ✓
- **Use case**: Successful operations

### Error Alert
```
┌─────────────────────────────────────────────────┐
│ ✕ Error Occurred               [Retry] ✕       │
├─────────────────────────────────────────────────┤
│ Failed to connect to the server                 │
├─────────────────────────────────────────────────┤
│ [██████████░░░░░░░░░░░░░░░░░░░] 2.5s          │
└─────────────────────────────────────────────────┘
```
- **Color**: Red (#ef4444)
- **Duration**: 5 seconds
- **Icon**: ✕
- **Use case**: Errors, dengan action button untuk retry

### Warning Alert
```
┌─────────────────────────────────────────────────┐
│ ⚠ Confirm Delete              [Delete] ✕       │
├─────────────────────────────────────────────────┤
│ This action cannot be undone                    │
├─────────────────────────────────────────────────┤
│ [███████░░░░░░░░░░░░░░░░░░░░░░] 2.0s          │
└─────────────────────────────────────────────────┘
```
- **Color**: Orange (#f59e0b)
- **Duration**: 4 seconds
- **Icon**: ⚠
- **Use case**: Important warnings

### Info Alert
```
┌─────────────────────────────────────────────────┐
│ ℹ New Features Available        [Learn More] ✕  │
├─────────────────────────────────────────────────┤
│ Check out our latest updates                    │
├─────────────────────────────────────────────────┤
│ [███░░░░░░░░░░░░░░░░░░░░░░░░░░] 1.0s          │
└─────────────────────────────────────────────────┘
```
- **Color**: Blue (#3b82f6)
- **Duration**: 3 seconds
- **Icon**: ℹ
- **Use case**: General information

## Stack Layout (Multiple Alerts)

```
┌─────────────────────────────────────────────┐
│ ✕ Error: Network failed       [Retry] ✕    │  ← Most recent
├─────────────────────────────────────────────┤
│ ⚠ Warning: Check your input         ✕      │  ← Previous
└─────────────────────────────────────────────┘

(12px gap between alerts)
```

## Responsive Mobile View

```
Mobile (< 640px):
┌──────────────────────────────────┐
│ ✓ Saved          │ [Retry]  ✕    │
├──────────────────────────────────┤
│ Data berhasil disimpan            │
├──────────────────────────────────┤
│ [████░░░░░░░░░░░░░░░░░░░░░░░░]  │
└──────────────────────────────────┘

Desktop (> 640px):
┌──────────────────────────────────────────────────────┐
│ ✓ Data Saved Successfully        [Retry]  ✕         │
├──────────────────────────────────────────────────────┤
│ Your changes have been saved                         │
├──────────────────────────────────────────────────────┤
│ [████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░]        │
└──────────────────────────────────────────────────────┘
```

## Animation Flow

### Slide In Animation
```
Step 1:         Step 2:         Step 3:
░░░░░░░░░░     ░░░░░░░░░░░     ┌─────────┐
░░░░░░░░░░  →  ░░░░░░░░░░░  →  │ Alert   │
░░░░░░░░░░     ┌─────────┐     └─────────┘
               │ Alert   │
               └─────────┘

Duration: 300ms
Easing: ease-out
Direction: translateX(400px) → 0
```

### Progress Bar Animation
```
Start (0%):       Mid (50%):        End (100%):
█████████████░░░░░ → ██████░░░░░░░░ → ░░░░░░░░░░░░░░
|═════════════════  |  ════════════  |  ═══════════════|
Duration: Depends on alert duration (e.g., 3s for success)
```

## Color System

```
SUCCESS (Green)
├─ Gradient: rgba(16, 185, 129, 0.05) → transparent
├─ Border: #10b981
├─ Icon BG: #10b981
└─ Progress Bar: #10b981

ERROR (Red)
├─ Gradient: rgba(239, 68, 68, 0.05) → transparent
├─ Border: #ef4444
├─ Icon BG: #ef4444
└─ Progress Bar: #ef4444

WARNING (Orange)
├─ Gradient: rgba(245, 158, 11, 0.05) → transparent
├─ Border: #f59e0b
├─ Icon BG: #f59e0b
└─ Progress Bar: #f59e0b

INFO (Blue)
├─ Gradient: rgba(59, 130, 246, 0.05) → transparent
├─ Border: #3b82f6
├─ Icon BG: #3b82f6
└─ Progress Bar: #3b82f6
```

## Interactive Elements

### Close Button
```
Idle:           Hover:          Click:
┌──────────────┐┌──────────────┐┌──────────────┐
│... Alert  ✕  ││... Alert  ✕  ││... Alert  ✕  │
└──────────────┘└──────────────┘└──────────────┘
                 BG: rgba(0,0,0,0.05)
                 Color changes
```

### Action Button
```
Idle:           Hover:          Active:
[Retry]         [Retry] ↓       [Retry] ✓
Color: Blue     BG: Blue/10     Color: Darker
```

## Accessibility

- ♿ Proper semantic HTML with `role="alert"`
- 📱 WCAG compliant color contrast
- ⌨️ Keyboard navigation support
- 🎨 Clear visual hierarchy
- 🔊 Screen reader friendly

## Example Compositions

### Login Error
```
┌─────────────────────────────────────────────┐
│ ✕ Login Failed                     ✕        │
├─────────────────────────────────────────────┤
│ Email or password is incorrect               │
└─────────────────────────────────────────────┘
```

### Successful Save
```
┌─────────────────────────────────────────────┐
│ ✓ Successfully Saved         ✕              │
├─────────────────────────────────────────────┤
│ Your changes have been saved                │
└─────────────────────────────────────────────┘
```

### Network Error with Action
```
┌─────────────────────────────────────────────┐
│ ✕ Connection Error          [Reconnect] ✕   │
├─────────────────────────────────────────────┤
│ Failed to connect. Check your network.      │
└─────────────────────────────────────────────┘
```

### Confirmation Delete
```
┌─────────────────────────────────────────────┐
│ ⚠ Are you sure?              [Delete] ✕     │
├─────────────────────────────────────────────┤
│ This action cannot be undone permanently    │
└─────────────────────────────────────────────┘
```

## Shadow & Depth

```
Alert box:
- Box Shadow: 0 8px 24px rgba(0, 0, 0, 0.12)
- Backdrop Filter: blur(10px)
- Elevation: High (9999 z-index)

Close Button:
- Hover: subtle background change
- Active: slightly darker
```

## Typography

```
Title (optional):
├─ Font: 14px, Weight: 600
├─ Color: #1e293b
└─ Line Height: 1.4

Message:
├─ Font: 14px, Weight: 400
├─ Color: #64748b
└─ Line Height: 1.4

Action Button:
├─ Font: 13px, Weight: 600
├─ Color: #3b82f6 (primary)
└─ All caps: NO
```

## Spacing

```
Container:
├─ Top/Right: 20px from viewport
├─ Gap between alerts: 12px
├─ Padding: 16px

Content:
├─ Icon to text: 12px
├─ Title to message: 4px
├─ Actions: 8px gap

Mobile:
├─ Left/Right: 12px from viewport
└─ Padding: 16px
```

## Size Specifications

```
Alert Box:
├─ Min Width: 250px (desktop), full-width (mobile)
├─ Max Width: 450px (desktop), 100% - 24px (mobile)
├─ Height: Auto (minimum ~60px with content)
├─ Border Radius: 10px

Icon:
├─ Size: 28x28px
├─ Border Radius: 6px
├─ Font Size: 16px

Close Button:
├─ Size: 18x18px (SVG)
└─ Hit area: 28x28px

Progress Bar:
├─ Height: 3px
├─ Position: Bottom of alert
└─ Width: 100% of alert
```

## Z-Index Hierarchy

```
999 (Background):
  ├─ Modal/Overlay
  │
9999 (Alert Container):
  ├─ Alert notifications
  └─ Fixed positioning
```

---

*This is a visual representation of the new Alert System design.*  
*For actual implementation details, see ALERT_SYSTEM.md*
