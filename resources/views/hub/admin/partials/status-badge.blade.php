@php
    $type = $type ?? 'default';
    $value = (string) ($value ?? '');

    $class = 'hub-badge';

    if ($type === 'company') {
        $class .= match ($value) {
            'active' => ' hub-badge--success',
            'suspended' => ' hub-badge--danger',
            default => ' hub-badge--warning',
        };
    }

    if ($type === 'financial') {
        $class .= match ($value) {
            'paid' => ' hub-badge--success',
            'overdue' => ' hub-badge--danger',
            'canceled' => ' hub-badge--muted',
            default => ' hub-badge--warning',
        };
    }

    if ($type === 'access') {
        $class .= $value === 'active' ? ' hub-badge--success' : ' hub-badge--muted';
    }

    $text = $label ?? strtoupper($value ?: '-');
@endphp

<span class="{{ $class }}">{{ $text }}</span>