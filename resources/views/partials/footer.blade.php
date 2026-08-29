<footer class="bg-white border-t border-gray-200 mt-auto py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-500">
        <div>
            &copy; {{ date('Y') }} Quirka. All rights reserved. Custom Framed Posters.
        </div>
        <div class="flex items-center gap-6 font-medium">
            <a href="{{ route('legal.shipping') }}" class="hover:text-gray-900 transition-colors">Shipping Policy</a>
            <a href="{{ route('legal.returns') }}" class="hover:text-gray-900 transition-colors">Return Policy</a>
            <a href="{{ route('legal.terms') }}" class="hover:text-gray-900 transition-colors">Terms of Service</a>
            <a href="{{ route('legal.privacy') }}" class="hover:text-gray-900 transition-colors">Privacy Policy</a>
        </div>
    </div>
</footer>
