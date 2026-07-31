import Menu from "./Menu";

export default function Header() {
    return (
        <header className="sticky top-0 w-full z-50 bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200">
            <div className="max-w-[1280px] mx-auto flex items-center justify-between px-6 py-4">

                <div className="flex items-center gap-3">
                    <span className="material-symbols-outlined text-indigo-600 cursor-pointer">
                        menu
                    </span>

                    <h1 className="text-3xl font-bold tracking-tight text-indigo-600">
                        CMS Management System
                    </h1>
                </div>

                <Menu />

            </div>
        </header>
    );
}