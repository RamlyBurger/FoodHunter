import { useState } from 'react'
import { supabase } from './supabaseClient'
import './App.css'

function App() {
  const [email, setEmail] = useState('')
  const [otp, setOtp] = useState('')
  const [step, setStep] = useState('send') // 'send' or 'verify'
  const [loading, setLoading] = useState(false)
  const [message, setMessage] = useState('')
  const [session, setSession] = useState(null)

  const handleSendOtp = async (e) => {
    e.preventDefault()
    setLoading(true)
    setMessage('')
    
    const { error } = await supabase.auth.signInWithOtp({ email })
    
    if (error) {
      setMessage(`Error: ${error.message}`)
    } else {
      setMessage('OTP sent! Check your email.')
      setStep('verify')
    }
    setLoading(false)
  }

  const handleVerifyOtp = async (e) => {
    e.preventDefault()
    setLoading(true)
    setMessage('')

    const { data, error } = await supabase.auth.verifyOtp({
      email,
      token: otp,
      type: 'email',
    })

    if (error) {
      setMessage(`Error: ${error.message}`)
    } else {
      setMessage('Login successful!')
      setSession(data.session)
      setStep('loggedIn')
    }
    setLoading(false)
  }

  const handleLogout = async () => {
    await supabase.auth.signOut()
    setSession(null)
    setStep('send')
    setEmail('')
    setOtp('')
    setMessage('')
  }

  if (step === 'loggedIn') {
    return (
      <div className="card">
        <h1>Welcome!</h1>
        <p>You are logged in as {session?.user?.email}</p>
        <button onClick={handleLogout}>Logout</button>
      </div>
    )
  }

  return (
    <div className="card">
      <h1>Supabase OTP Login</h1>
      
      {step === 'send' ? (
        <form onSubmit={handleSendOtp}>
          <div style={{ marginBottom: '1rem' }}>
            <input
              type="email"
              placeholder="Enter your email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              style={{ padding: '0.5rem', marginRight: '0.5rem' }}
            />
          </div>
          <button type="submit" disabled={loading}>
            {loading ? 'Sending...' : 'Send OTP'}
          </button>
        </form>
      ) : (
        <form onSubmit={handleVerifyOtp}>
           <div style={{ marginBottom: '1rem' }}>
            <p>Sent to: {email}</p>
            <input
              type="text"
              placeholder="Enter OTP"
              value={otp}
              onChange={(e) => setOtp(e.target.value)}
              required
              style={{ padding: '0.5rem', marginRight: '0.5rem' }}
            />
          </div>
          <button type="submit" disabled={loading}>
            {loading ? 'Verifying...' : 'Verify OTP'}
          </button>
          <button 
            type="button" 
            onClick={() => setStep('send')}
            style={{ marginLeft: '0.5rem', backgroundColor: '#666' }}
          >
            Back
          </button>
        </form>
      )}

      {message && <p style={{ marginTop: '1rem' }}>{message}</p>}
    </div>
  )
}

export default App
