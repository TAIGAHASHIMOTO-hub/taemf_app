import type { Metadata } from 'next'
import { Noto_Sans_JP } from 'next/font/google'
import './globals.css'
import Header from '../components/Header'
import Footer from '../components/Footer'

const notoSansJP = Noto_Sans_JP({
  subsets: ['latin'],
  weight: ['100', '200', '300', '400', '500', '700'],
  display: 'swap',
})

export const metadata: Metadata = {
  title: 'LUNALEA - ECサイト',
  description: 'LUNALEA ECサイト',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="ja">
      <body className={notoSansJP.className}>
        <Header />
        <main>{children}</main>
        <Footer />
      </body>
    </html>
  )
}