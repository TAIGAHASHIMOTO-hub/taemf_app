'use client'

import { useState, useEffect } from 'react'
import Image from 'next/image'

export default function HomePage() {
  const [slideIndex, setSlideIndex] = useState(1)
  const [currentEllipseSlide, setCurrentEllipseSlide] = useState(0)
  const [showBackToTop, setShowBackToTop] = useState(false)
  const totalSlides = 6

  // スライドショー用画像配列
  const slideImages = [
    '/img/trends.svg',
    '/img/aitem.svg',
    '/img/fuku.svg',
    '/img/trends.svg',
    '/img/aitem.svg',
    '/img/ranking.svg'
  ]

  // おすすめセット用画像配列
  const mainImages = [
    '/img/image.svg',
    '/img/set2.jpg',
    '/img/set3.jpg',
    '/img/set4.jpg',
    '/img/set1.jpg'
  ]

  // 自動スライドショー
  useEffect(() => {
    const slideTimer = setInterval(() => {
      setSlideIndex(prev => prev >= totalSlides ? 1 : prev + 1)
    }, 4000)

    return () => clearInterval(slideTimer)
  }, [totalSlides])

  // スクロール監視（一番上に戻るボタン）
  useEffect(() => {
    const handleScroll = () => {
      if (window.pageYOffset > 300) {
        setShowBackToTop(true)
      } else {
        setShowBackToTop(false)
      }
    }

    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  // スライド更新
  const updateSlides = (index: number) => {
    setSlideIndex(index)
  }

  // 楕円スライダー更新
  const updateEllipseSlider = (index: number) => {
    setCurrentEllipseSlide(index)
  }

  // 一番上に戻る
  const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    })
  }

  const prevIndex = slideIndex === 1 ? totalSlides : slideIndex - 1
  const nextIndex = slideIndex === totalSlides ? 1 : slideIndex + 1

  return (
    <div className="min-h-screen">
      {/* メインスライドショー */}
      <div className="slideshow-wrapper">
        <div className="slideshow-container">
          {/* 左の画像 */}
          <div
            className="slide-left"
            onClick={() => updateSlides(prevIndex)}
          >
            <Image
              src={slideImages[prevIndex - 1]}
              alt="Previous"
              fill
              className="object-cover"
            />
          </div>

          {/* 中央の画像 */}
          <div className="slide-center">
            <Image
              src={slideImages[slideIndex - 1]}
              alt="Current"
              width={1200}
              height={651}
              className="object-cover"
            />
          </div>

          {/* 右の画像 */}
          <div
            className="slide-right"
            onClick={() => updateSlides(nextIndex)}
          >
            <Image
              src={slideImages[nextIndex - 1]}
              alt="Next"
              fill
              className="object-cover"
            />
          </div>
        </div>

        {/* インジケーター */}
        <div className="indicators">
          {Array.from({ length: totalSlides }, (_, i) => (
            <div
              key={i}
              className={`indicator ${slideIndex === i + 1 ? 'active' : ''}`}
              onClick={() => updateSlides(i + 1)}
            ></div>
          ))}
        </div>
      </div>

      {/* おすすめセット商品セクション */}
      <section className="recommended-section">
        <div className="section-title">
          <div className="title-ribbon">おすすめセット商品</div>
        </div>

        <div className="set-container">
          <div className="con-right">
            <div className="set-main">
              <Image
                src={mainImages[currentEllipseSlide]}
                alt="メイン画像"
                width={672}
                height={392}
                className="main-image"
              />
              <div className="ellipse-slider-wrapper">
                <button
                  className="arrow left-arrow"
                  onClick={() => updateEllipseSlider(
                    currentEllipseSlide > 0 ? currentEllipseSlide - 1 : mainImages.length - 1
                  )}
                >
                  <Image src="/img/yazirusi.svg" alt="左矢印" width={30} height={30} />
                </button>

                <div className="ellipse-slider">
                  {mainImages.map((image, index) => (
                    <Image
                      key={index}
                      src={image}
                      alt={`楕円${index + 1}`}
                      width={80}
                      height={80}
                      className={`ellipse-img ${currentEllipseSlide === index ? 'active' : ''}`}
                      onClick={() => updateEllipseSlider(index)}
                    />
                  ))}
                </div>

                <button
                  className="arrow right-arrow"
                  onClick={() => updateEllipseSlider(
                    currentEllipseSlide < mainImages.length - 1 ? currentEllipseSlide + 1 : 0
                  )}
                >
                  <Image src="/img/yazirusi.svg" alt="右矢印" width={30} height={30} />
                </button>
              </div>
            </div>
          </div>

          <div className="con-left">
            <div className="set-thumbs-grid">
              <Image src="/img/image.svg" alt="set1" width={232} height={227} className="thumb-img" />
              <Image src="/img/set2.jpg" alt="set2" width={232} height={227} className="thumb-img" />
              <Image src="/img/set3.jpg" alt="set3" width={232} height={227} className="thumb-img" />
              <Image src="/img/set4.jpg" alt="set4" width={232} height={227} className="thumb-img" />
            </div>
          </div>
        </div>
      </section>

      {/* 商品セクション */}
      <section className="items-pick">
        {/* トレンドセクション */}
        <section className="trend-section">
          <div className="demo-section">
            <div className="trend-section-title">トレンド</div>
            <div className="item-cards">
              {Array.from({ length: 5 }, (_, i) => (
                <div key={i} className="item-card">
                  <a>
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
                      <p className="item-name">フリル付きVネックノースリーブブラウス</p>
                      <p className="item-brand">jemirmic</p>
                      <p className="item-price">¥11,700</p>
                    </div>
                  </a>
                </div>
              ))}
            </div>
            <div className="item-boder"></div>
          </div>
        </section>

        {/* セールセクション */}
        <section className="trend-section">
          <div className="demo-section">
            <div className="trend-section-title trend">セール</div>
            <div className="item-cards">
              {Array.from({ length: 5 }, (_, i) => (
                <div key={i} className="item-card">
                  <a>
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
                      <p className="item-name">フリル付きVネックノースリーブブラウス</p>
                      <p className="item-brand">jemirmic</p>
                      <p className="item-price">¥11,700</p>
                    </div>
                  </a>
                </div>
              ))}
            </div>
            <div className="item-boder"></div>
          </div>
        </section>

        {/* おすすめセクション */}
        <section className="recommendation-section">
          <div className="demo-section">
            <div className="trend-section-title">おすすめ</div>
            <div className="item-cards">
              {Array.from({ length: 5 }, (_, i) => (
                <div key={i} className="item-card">
                  <a>
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
                      <p className="item-name">フリル付きVネックノースリーブブラウス</p>
                      <p className="item-brand">jemirmic</p>
                      <p className="item-price">¥11,700</p>
                    </div>
                  </a>
                </div>
              ))}
            </div>
            <div className="item-boder"></div>
          </div>
        </section>
      </section>
      {/* 一番上に戻るボタン */}
      <button
        className={`back-to-top ${showBackToTop ? 'show' : ''}`}
        onClick={scrollToTop}
        aria-label="一番上に戻る"
      >
        <Image src="/img/back.svg" alt="戻る" width={50} height={50} />
      </button>
    </div>
  )
}