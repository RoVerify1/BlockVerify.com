// pages/api/generate.js (Next.js/Vercel)
import OpenAI from 'openai';

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { prompt } = req.body;

  try {
    const completion = await openai.chat.completions.create({
      model: "gpt-4o",
      messages: [
        { role: "system", content: "Du bist ein Experte für Roblox Lua scripting. Generiere nur den Lua-Code, keine Erklärungen. Nutze moderne Roblox APIs (z.B. task.wait statt wait)." },
        { role: "user", content: prompt }
      ],
    });

    const code = completion.choices[0].message.content;
    res.status(200).json({ code });
  } catch (error) {
    res.status(500).json({ error: 'Fehler bei der Generierung' });
  }
}