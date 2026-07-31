export default function Footer() {
    return (
        <footer className="border-t bg-white mt-16">
            <div className="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between">
                <div>
                    <h2 className="text-xl font-bold text-indigo-600">
                        CMS Management System
                    </h2>
                    <p className="text-sm text-gray-500 mt-1">
                        © {new Date().getFullYear()} CMS Management System. All rights reserved.
                    </p>
                </div>

                <div className="flex items-center gap-4 mt-4 md:mt-0">
                    <a
                        href="#"
                        className="text-gray-500 hover:text-indigo-600 transition"
                    >
                        Home
                    </a>

                    <a
                        href="#"
                        className="text-gray-500 hover:text-indigo-600 transition"
                    >
                        Pages
                    </a>
                </div>
            </div>
        </footer>
    );
}