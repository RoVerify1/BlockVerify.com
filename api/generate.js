export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const { prompt } = req.body;

  try {
    // NUTZE DIE NEUE ROUTER URL
    const response = await fetch(
      "https://router.huggingface.co/hf-inference/models/bigcode/starcoder2-3b", 
      {
        headers: {
          Authorization: `Bearer ${process.env.HF_API_KEY}`,
          "Content-Type": "application/json",
        },
        method: "POST",
        body: JSON.stringify({
          inputs: `-- Roblox Lua Script\ -- Task: ${prompt}\n-- Code:`,
          parameters: {
            max_new_tokens: 250,
            temperature: 0.2, // Niedriger = präziserer Code
            top_p: 0.9,
            return_full_text: false
          }
        }),
      }
    );

    const result = await response.json();

    // Check, ob das Modell gerade erst geladen wird (Hugging Face Spezialität)
    if (result.estimated_time) {
        return res.status(503).json({ error: "Modell lädt noch... bitte in 20 Sek. versuchen." });
    }

    if (result.error) {
      return res.status(500).json({ error: result.error });
    }

    // Falls das Modell ein Array zurückgibt
    const generatedCode = Array.isArray(result) ? result[0].generated_text : result.generated_text;
    
    res.status(200).json({ code: generatedCode || "-- Kein Code generiert" });

  } catch (err) {
    res.status(500).json({ error: "Server Fehler: " + err.message });
  }
}
