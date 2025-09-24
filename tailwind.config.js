/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./resources/views/**/*.blade.php",
      "./resources/js/**/*.js",
      "./app/View/**/*.php",
      "./app/Livewire/**/*.php",
    ],
    theme: { extend: {} },
    safelist: [
      // add any classes built dynamically or from conditions
      "bg-gray-800","bg-gray-900","bg-slate-700","bg-slate-800",
      "text-gray-100","text-gray-300","text-slate-100","text-slate-300",
      "border","border-gray-700","rounded","rounded-lg","rounded-xl",
      "hover:bg-gray-700","hover:bg-slate-700","text-xs","text-sm",
      "grid","flex","items-center","justify-between",
      // status colors you toggle in Blade:
      "bg-blue-500","border-blue-600","bg-red-500","border-red-600","text-blue-400","text-red-400",
    ],
  }
