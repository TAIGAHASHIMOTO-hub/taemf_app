'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import Image from 'next/image'

export default function Header() {
    const [isMenuOpen, setIsMenuOpen] = useState(false)
    const [isCategoryOpen, setIsCategoryOpen] = useState(false)

    const toggleMenu = () => {
        setIsMenuOpen(!isMenuOpen)
        if (!isMenuOpen) {
            document.body.style.overflow = 'hidden'
        } else {
            document.body.style.overflow = ''
            setIsCategoryOpen(false)
        }
    }

    const closeMenu = () => {
        setIsMenuOpen(false)
        setIsCategoryOpen(false)
        document.body.style.overflow = ''
    }

    const toggleCategory = (e: React.MouseEvent) => {
        e.preventDefault()
        setIsCategoryOpen(!isCategoryOpen)
    }

    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                closeMenu()
            }
        }

        document.addEventListener('keydown', handleKeyDown)
        return () => {
            document.removeEventListener('keydown', handleKeyDown)
        }
    }, [])

    return (
        <>
            <header className="header">
                <button className="menu-button" onClick={toggleMenu}>
                    <div className="menu-icon">
                        <div className="menu-line"></div>
                        <div className="menu-line"></div>
                        <div className="menu-line"></div>
                    </div>
                </button>

                <div className="logo-icon">
                    <Link href="/">
                        <Image
                            src="/img/logo.svg"
                            alt="LUNALEA"
                            width={80}
                            height={80}
                            priority
                        />
                    </Link>
                </div>

                <div className="header-icons">
                    <Link href="/login">
                        <button>
                            <Image
                                src="/img/usericon.svg"
                                alt="ユーザー"
                                width={40}
                                height={40}
                            />
                        </button>
                    </Link>
                    <Link href="/favorites">
                        <button>
                            <Image
                                src="/img/likeicon.svg"
                                alt="お気に入り"
                                width={40}
                                height={40}
                            />
                        </button>
                    </Link>
                    <Link href="/cart">
                        <button>
                            <Image
                                src="/img/carticon.svg"
                                alt="カート"
                                width={40}
                                height={40}
                            />
                        </button>
                    </Link>
                </div>
            </header>

            <div
                className={`overlay ${isMenuOpen ? 'active' : ''}`}
                onClick={closeMenu}
            ></div>

            <nav className={`side-menu ${isMenuOpen ? 'active' : ''}`}>
                <div className="menu-header">
                    <button className="close-button" onClick={closeMenu}>
                        <div className="close-icon"></div>
                    </button>
                </div>

                <div className="menu-list">
                    <div className="menu-item">
                        <div className="menu-item-content">
                            <Link href="/register" className="menu-link">
                                <span className="menu-text">会員登録</span>
                            </Link>
                        </div>
                    </div>

                    <div className="menu-item">
                        <div className="menu-item-content">
                            <Link href="/login" className="menu-link">
                                <span className="menu-text">ログイン</span>
                            </Link>
                        </div>
                    </div>

                    <div className="menu-item">
                        <div className="menu-item-content">
                            <Link href="/favorites" className="menu-link">
                                <span className="menu-text">お気に入り</span>
                            </Link>
                        </div>
                    </div>

                    <div className="menu-item">
                        <div className="menu-item-content no-bottom-borders">
                            <a
                                className="menu-link"
                                onClick={toggleCategory}
                                style={{ cursor: 'pointer' }}
                            >
                                <span className="menu-text">カテゴリーから探す</span>
                                <div className={`dropdown-icon ${isCategoryOpen ? 'active' : ''}`}>
                                    <Image src="/img/Vector.svg" alt="" width={12} height={6} />
                                </div>
                            </a>
                        </div>
                        <div className={`category-dropdown ${isCategoryOpen ? 'active' : ''}`}>
                            <div className="category-item">
                                <Link href="/products?category=formal" className="category-link">
                                    <span>フォーマルドレス</span>
                                </Link>
                            </div>
                            <div className="category-item">
                                <Link href="/products?category=casual" className="category-link">
                                    <span>カジュアルドレス</span>
                                </Link>
                            </div>
                            <div className="category-item">
                                <Link href="/products?category=wedding" className="category-link">
                                    <span>ウェディングドレス</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="menu-item">
                        <div className="menu-item-content">
                            <Link href="/cart" className="menu-link line-boder">
                                <span className="menu-text">カート</span>
                            </Link>
                        </div>
                    </div>

                    <div className="menu-item">
                        <div className="menu-item-content">
                            <Link href="/" className="menu-link">
                                <span className="menu-text">トップへ</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>
        </>
    )
}