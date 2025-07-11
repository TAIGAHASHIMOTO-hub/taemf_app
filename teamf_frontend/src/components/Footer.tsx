import Link from 'next/link'

export default function Footer() {
    return (
        <footer className="footer">
            <Link href="/terms" className="footer-link">
                利用規約
            </Link>
            <div className="copyright">©LUNALEA.</div>
        </footer>
    )
}
