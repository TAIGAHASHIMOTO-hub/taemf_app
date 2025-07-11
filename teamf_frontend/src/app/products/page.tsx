'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import Image from 'next/image'

export default function ProductsPage() {
    const [searchTerm, setSearchTerm] = useState('')
    const [sortBy, setSortBy] = useState('new')
    const [showSortDropdown, setShowSortDropdown] = useState(false)
    const [isCategoryOpen, setIsCategoryOpen] = useState(false)
    const [selectedCategories, setSelectedCategories] = useState<string[]>([])
    const [minPrice, setMinPrice] = useState(0)
    const [maxPrice, setMaxPrice] = useState(50000)
    const [currentPage, setCurrentPage] = useState(1)
    const [showBackToTop, setShowBackToTop] = useState(false)

    const totalPages = 5

    // ソートオプション
    const sortOptions = [
        { value: 'new', label: '新商品順' },
        { value: 'recommended', label: 'おすすめ順' },
        { value: 'popular', label: '人気順' },
        { value: 'price-low', label: '価格が安い順' },
        { value: 'price-high', label: '価格が高い順' },
    ]

    // カテゴリオプション
    const categories = [
        { value: 'formal', label: 'フォーマルドレス' },
        { value: 'casual', label: 'カジュアルドレス' },
        { value: 'wedding', label: 'ウェディングドレス' },
        { value: 'evening', label: 'イブニングドレス' },
        { value: 'cocktail', label: 'カクテルドレス' },
        { value: 'daily', label: 'デイリードレス' },
        { value: 'summer', label: 'サマードレス' },
        { value: 'aline', label: 'Aライン' },
    ]

    // スクロール監視
    useEffect(() => {
        const handleScroll = () => {
            setShowBackToTop(window.pageYOffset > 300)
        }
        window.addEventListener('scroll', handleScroll)
        return () => window.removeEventListener('scroll', handleScroll)
    }, [])

    // カテゴリ選択処理
    const handleCategoryChange = (categoryValue: string) => {
        setSelectedCategories(prev =>
            prev.includes(categoryValue)
                ? prev.filter(cat => cat !== categoryValue)
                : [...prev, categoryValue]
        )
    }

    // カテゴリ表示テキスト
    const getCategoryDisplayText = () => {
        if (selectedCategories.length === 0) {
            return (
                <>
                    <span className="category-main">カテゴリー</span>
                    <span className="category-sub">から探す</span>
                </>
            )
        } else if (selectedCategories.length === 1) {
            const selectedCategory = categories.find(cat => cat.value === selectedCategories[0])
            return <span className="category-main">{selectedCategory?.label}</span>
        } else {
            return <span className="category-main">{selectedCategories.length}件選択中</span>
        }
    }

    // 価格範囲更新
    const updatePriceDisplay = () => {
        if (minPrice >= maxPrice) {
            setMinPrice(maxPrice - 1000)
        }
    }

    // 検索・フィルター適用
    const applyFilters = () => {
        const selectedCategoryLabels = selectedCategories.map(value =>
            categories.find(cat => cat.value === value)?.label
        ).filter(Boolean)

        console.log('絞り込み条件:', {
            categories: selectedCategoryLabels,
            minPrice,
            maxPrice,
            searchTerm,
            sortBy
        })
    }

    // ページネーション
    const handlePageChange = (page: number) => {
        setCurrentPage(page)
    }

    // 一番上に戻る
    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }

    return (
        <div className="products-container">
            {/* パンくずリスト */}
            <nav className="breadcrumb">
                <Link href="/">HOME</Link> / ドレス
            </nav>

            {/* ページタイトル */}
            <h1 className="page-title">ドレス</h1>

            {/* 検索・ソート */}
            <div className="search-select-container">
                <div className="search-container">
                    <input
                        type="text"
                        className="search-input"
                        placeholder="検索"
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                    <button className="search-btn" type="submit">
                        <Image src="/img/kensaku.svg" alt="検索" width={18} height={18} className="search-icon" />
                    </button>
                </div>

                <div className="select-container">
                    <div
                        className={`select-box ${showSortDropdown ? 'open' : ''}`}
                        onClick={() => setShowSortDropdown(!showSortDropdown)}
                    >
                        {sortOptions.find(option => option.value === sortBy)?.label}
                        <Image
                            src="/img/Vector.svg"
                            alt="矢印"
                            width={12}
                            height={12}
                            className="select-arrow"
                        />
                    </div>
                    <div className={`select-dropdown ${showSortDropdown ? 'show' : ''}`}>
                        {sortOptions.map(option => (
                            <div
                                key={option.value}
                                className={`select-option ${sortBy === option.value ? 'selected' : ''}`}
                                onClick={() => {
                                    setSortBy(option.value)
                                    setShowSortDropdown(false)
                                }}
                            >
                                {option.label}
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <section className="goods">
                {/* フィルター */}
                <div className="filter-container">
                    <h3 className="filter-title">絞り込み</h3>

                    {/* カテゴリー選択 */}
                    <div className="filter-section">
                        <div className="menu-item">
                            <div className="menu-item-content no-bottom-borders">
                                <a
                                    className="menu-link"
                                    onClick={() => setIsCategoryOpen(!isCategoryOpen)}
                                    style={{ cursor: 'pointer' }}
                                >
                                    <span className="menu-text">
                                        {getCategoryDisplayText()}
                                    </span>
                                    <div className={`dropdown-icon ${isCategoryOpen ? 'active' : ''}`}>
                                        <Image src="/img/Vector.svg" alt="" width={12} height={12} />
                                    </div>
                                </a>
                            </div>
                            <div className={`category-dropdown ${isCategoryOpen ? 'active' : ''}`}>
                                {categories.map(category => (
                                    <div key={category.value} className="category-item">
                                        <a
                                            href="#"
                                            className={`category-link ${selectedCategories.includes(category.value) ? 'selected' : ''}`}
                                            onClick={(e) => {
                                                e.preventDefault()
                                                handleCategoryChange(category.value)
                                            }}
                                        >
                                            <input
                                                type="checkbox"
                                                className="category-checkbox"
                                                checked={selectedCategories.includes(category.value)}
                                                onChange={() => handleCategoryChange(category.value)}
                                            />
                                            <span>{category.label}</span>
                                        </a>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* 価格範囲 */}
                    <div className="filter-section">
                        <div className="price-section">
                            <label className="price-label">
                                <span className="price-main">価格</span>
                                <span className="price-sub">から探す</span>
                            </label>
                            <div className="price-slider-container">
                                <div className="price-range-slider">
                                    <div
                                        className="price-range-track"
                                        style={{
                                            left: `${(minPrice / 50000) * 100}%`,
                                            width: `${((maxPrice - minPrice) / 50000) * 100}%`
                                        }}
                                    ></div>
                                    <input
                                        type="range"
                                        min="0"
                                        max="50000"
                                        step="1000"
                                        value={minPrice}
                                        onChange={(e) => setMinPrice(Number(e.target.value))}
                                        onInput={updatePriceDisplay}
                                    />
                                    <input
                                        type="range"
                                        min="0"
                                        max="50000"
                                        step="1000"
                                        value={maxPrice}
                                        onChange={(e) => setMaxPrice(Number(e.target.value))}
                                        onInput={updatePriceDisplay}
                                    />
                                </div>
                                <button className="search-button" onClick={applyFilters}>
                                    <span>検索</span>
                                    <Image src="/img/kensakudrat.svg" alt="検索" width={12} height={12} />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 商品一覧 */}
                <div className="item-goods">
                    {Array.from({ length: 4 }, (_, rowIndex) => (
                        <div key={rowIndex} className="item-cards">
                            {Array.from({ length: 4 }, (_, colIndex) => (
                                <div key={colIndex} className="item-card">
                                    <Link href={`/products/${rowIndex * 4 + colIndex + 1}`}>
                                        <div className="image-container">
                                            <Image
                                                src="/img/img.svg"
                                                alt="商品画像"
                                                width={220}
                                                height={270}
                                                className="item-card-img main-img"
                                            />
                                            <Image
                                                src="/img/image.svg"
                                                alt="商品画像2"
                                                width={220}
                                                height={270}
                                                className="item-card-img hover-img"
                                            />
                                        </div>
                                        <div className="item-text">
                                            <p className="item-name">エレガントドレス</p>
                                            <p className="item-brand">LUNALEA</p>
                                            <p className="item-price">¥25,800</p>
                                        </div>
                                    </Link>
                                </div>
                            ))}
                        </div>
                    ))}
                </div>
            </section>

            {/* ページネーション */}
            <div className="pagination">
                <button
                    className="pagination-arrow"
                    disabled={currentPage === 1}
                    onClick={() => handlePageChange(currentPage - 1)}
                >
                    <Image src="/img/yazirusi.svg" alt="前へ" width={30} height={28} />
                </button>

                {Array.from({ length: totalPages }, (_, i) => (
                    <button
                        key={i + 1}
                        className={`pagination-number ${currentPage === i + 1 ? 'active' : ''}`}
                        onClick={() => handlePageChange(i + 1)}
                    >
                        {i + 1}
                    </button>
                ))}

                <button
                    className="pagination-arrow right"
                    disabled={currentPage === totalPages}
                    onClick={() => handlePageChange(currentPage + 1)}
                >
                    <Image src="/img/yazirusi.svg" alt="次へ" width={30} height={28} />
                </button>
            </div>

            {/* 一番上に戻るボタン */}
            <button
                className={`back-to-top ${showBackToTop ? 'show' : ''}`}
                onClick={scrollToTop}
                aria-label="一番上に戻る"
            >
                <Image src="/img/back.svg" alt="戻る" width={30} height={35} />
            </button>
        </div>
    )
}