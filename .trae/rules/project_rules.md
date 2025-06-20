# Project Rules for Laravel + Vue Starter Kit

## Project Overview

This is a Laravel + Vue project that utilizes:

- Vue 3 with TypeScript
- shadcn-vue UI components
- Lucide icons
- Tailwind CSS for styling
- Inertia.js for server-client communication
- Always use NPM and/or COMPOSER

## Code Organization

### Laravel Architecture

1. **Routes**
    - Define routes in `routes/web.php`
    - Use resourceful routes for common CRUD operations
    - Use `Route::get()` for simple pages
2. **Controllers**
    - Place controllers in `app/Http/Controllers`
    - Use resourceful controllers for common CRUD operations
    - Use `Route::resource()` for resourceful routes
    - Use `Route::get()` for simple pages
    - Use `Route::post()` for form submissions
    - Use `Route::put()` for update requests
    - Use `Route::delete()` for delete requests
3. **Models**
    - Implement models in `app/Models`
    - Use Eloquent ORM for database interactions
    - Use relationships for associations
    - Use Eloquent's query builder for complex queries
4. **Views**
    - Place views in `resources/views`

### Vue Components

1. **Component Structure**

    - Use TypeScript for all Vue components
    - Follow the `<script setup lang="ts">` pattern
    - Place components in appropriate directories:
        - UI components: `resources/js/components/ui`
        - Layout components: `resources/js/components`
        - Pages: `resources/js/pages`

2. **Component Naming**

    - Use PascalCase for component names
    - Use kebab-case for file names
    - Prefix layout components with 'App' (e.g., AppHeader, AppSidebar)
    - Suffix common component variations with their purpose

3. **Props and Events**
    - Define prop types explicitly using TypeScript interfaces
    - Use `withDefaults` for default prop values
    - Emit typed events using `defineEmits`

### TypeScript Usage

1. **Type Definitions**

    - Place shared interfaces and types in `resources/js/types`
    - Use explicit type annotations for function parameters and returns
    - Avoid `any` type - use proper type definitions

2. **Type Imports**
    - Use type imports when importing only types: `import type { ComponentType } from '@/types'`
    - Maintain clean separation between type imports and value imports

### UI Components

1. **shadcn-vue Components**

    - Use shadcn-vue components as base UI elements
    - Follow the component installation process via CLI
    - Customize components through the provided configuration options
    - Place customizations in `components.json`
    - DO NOT modify the PROVIDED shadcn-vue components in the `components` directory

2. **Icons**
    - Use Lucide icons consistently throughout the application
    - Import icons from `components/ui/icons` and use them as components
    - Follow the icon naming convention in the design system

### Styling

1. **Tailwind CSS**

    - Use Tailwind's utility classes for styling
    - Follow the project's color scheme defined in the theme
    - Utilize the shadcn-vue design tokens for consistency
    - Maintain dark mode support using `dark:` variants

2. **CSS Organization**

    - Place global styles in `resources/css/app.css`
    - Use component-specific styles within components when necessary
    - Leverage Tailwind's @apply directive for repeated utility patterns

3. **Responsive Design**

    - Use Tailwind's responsive design utilities
    - Implement breakpoints for different screen sizes
    - Use mobile-first design principles

4. **Finova Styling Guidelines**
    - Minimalist and modern design
    - Use `--font-serif` for heading and `--font-sans` for body text
    - The target users are mostly university students, who want to have a simple and clean design

## State Management

1. **Inertia.js**

    - Use Inertia.js for handling server-side state
    - Leverage `usePage()` for accessing shared data

2. **Component State**
    - Use `ref` and `reactive` for local state management
    - Implement `computed` properties for derived state
    - Use composables for shared stateful logic

## Best Practices

1. **Performance**

    - Implement lazy loading for routes and heavy components
    - Use `v-show` instead of `v-if` for frequently toggled elements
    - Optimize component re-renders using `v-memo` when appropriate

2. **Code Quality**

    - Follow ESLint and Prettier configurations
    - Write meaningful component and function documentation
    - Use TypeScript strict mode features
    - Maintain consistent code formatting

3. **Security**
    - Validate all user inputs
    - Use Laravel's CSRF protection
    - Implement proper authentication checks
    - Follow Laravel security best practices
