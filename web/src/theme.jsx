import React, { useEffect, useState } from 'react'
import { api } from './api.js'

// Panel themes (owner 2026-11-15): paint the brand + theme values, and the picker used by "Name and look" and "My account > Look".
export function paintBrand(b) {
  if (!b) return
  const r = document.documentElement.style
  const set = (k, v) => (v || v === 0 ? r.setProperty(k, String(v)) : r.removeProperty(k))
  set('--brand', b.color)
  set('--pt-side', b.side); set('--pt-side-2', b.side2); set('--pt-side-text', b.side_text); set('--pt-side-muted', b.side_muted)
  set('--pt-bg', b.bg); set('--pt-radius', b.radius !== undefined && b.radius !== null ? b.radius + 'px' : null)
}

// value: { theme, color, side, bg, radius }; onChange(next). Shows the themes as cards and the colours on top.
export function ThemePicker({ value, onChange }) {
  const [themes, setThemes] = useState(null)
  useEffect(() => { api('/themes').then((x) => setThemes(x.themes)).catch(() => setThemes({})) }, [])
  if (!themes) return null
  const v = value || {}
  const cur = themes[v.theme || 'midnight'] || {}
  const pick = (k) => onChange({ ...v, theme: k, side: undefined, bg: undefined, radius: undefined }) // a new theme starts from its own shading
  return (
    <div>
      <div className="theme-cards" role="radiogroup" aria-label="Theme">
        {Object.entries(themes).map(([k, t]) => (
          <button type="button" key={k} role="radio" aria-checked={(v.theme || 'midnight') === k} className={'theme-card' + ((v.theme || 'midnight') === k ? ' on' : '')} onClick={() => pick(k)}>
            <span className="theme-mini" style={{ background: t.bg, borderRadius: Math.min(t.radius, 10) }}><i style={{ background: t.side }} /><b style={{ background: v.color || '#2563eb' }} /><em /></span>
            <span>{t.label}</span>
          </button>
        ))}
      </div>
      <div className="form" style={{ marginTop: 12 }}>
        <label>Main colour<input type="color" value={v.color || '#2563eb'} onChange={(e) => onChange({ ...v, color: e.target.value })} style={{ height: 42 }} /></label>
        <label>Sidebar colour<input type="color" value={v.side || cur.side || '#0f1530'} onChange={(e) => onChange({ ...v, side: e.target.value })} style={{ height: 42 }} /></label>
        <label>Page colour<input type="color" value={v.bg || cur.bg || '#f3f5fb'} onChange={(e) => onChange({ ...v, bg: e.target.value })} style={{ height: 42 }} /></label>
        <label>Corners: {v.radius ?? cur.radius ?? 14}px<input type="range" min="0" max="24" value={v.radius ?? cur.radius ?? 14} onChange={(e) => onChange({ ...v, radius: Number(e.target.value) })} /></label>
      </div>
    </div>
  )
}

// "My account > Look": a user's own theme over the panel's (only for this user)
export function MyLook({ me, onSaved }) {
  const [f, setF] = useState({ theme: me.brand.theme, color: me.brand.color, side: me.brand.side, bg: me.brand.bg, radius: me.brand.radius })
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')
  const save = (e) => { e.preventDefault(); setErr(''); setMsg(''); api('/me/look', f).then((r) => { paintBrand(r.brand); setMsg('Saved: your panel now looks like this.'); onSaved && onSaved() }).catch((x) => setErr(x.message)) }
  const reset = () => api('/me/look', {}).then((r) => { paintBrand(r.brand); setF({ theme: r.brand.theme, color: r.brand.color, side: r.brand.side, bg: r.brand.bg, radius: r.brand.radius }); setMsg('Back to the panel\'s look.') })
  return (
    <form className="card" onSubmit={save}>
      <h2>Look</h2><p className="muted small">Your own theme and colours for this panel (only you see them).</p>
      {err && <div className="alert bad">{err}</div>}{msg && <div className="alert good">{msg}</div>}
      <ThemePicker value={f} onChange={(x) => { setF(x); paintBrand({ ...me.brand, ...x, side: x.side, bg: x.bg }) }} />
      <div style={{ marginTop: 12, display: 'flex', gap: 8, flexWrap: 'wrap' }}><button className="btn primary">Save my look</button><button type="button" className="btn" onClick={reset}>Use the panel's look</button></div>
    </form>
  )
}
