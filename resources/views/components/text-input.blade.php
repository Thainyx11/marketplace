@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:border-violet-500 dark:focus:border-violet-500 focus:ring-violet-500 dark:focus:ring-violet-500 rounded-lg shadow-sm text-sm']) }}>
